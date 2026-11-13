<?php

namespace App\Services\Content;

use App\Models\Category;
use App\Models\Tag;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BlogTerms
{
    public function model(string $kind): Category|Tag
    {
        return match ($kind) {
            'categories' => new Category,
            'tags' => new Tag,
            default => abort(404),
        };
    }

    public function find(string $kind, int $id, bool $archived = false): Category|Tag
    {
        $query = $this->model($kind)->newQuery();

        return ($archived ? $query->withTrashed() : $query)->findOrFail($id);
    }

    public function referenced(Category|Tag $term): bool
    {
        return $term instanceof Category ? DB::table('posts')->where('category_id', $term->id)->exists()
            : DB::table('post_tag')->where('tag_id', $term->id)->exists();
    }

    /** @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public function validate(Category|Tag $term, array $values): array
    {
        $plain = function (string $attribute, mixed $value, Closure $fail): void {
            if (is_string($value) && preg_match('/[<>\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
                $fail('Use plain text without HTML or control characters.');
            }
        };
        $data = Validator::make($values, ['name' => ['required', 'string', 'max:255', $plain],
            'slug' => ['required', 'string', 'max:255', 'regex:/^(?:[\p{L}\p{N}_-]|%[0-9a-fA-F]{2})+$/uD'],
            'path' => ['required', 'string', 'max:768'], 'description' => ['nullable', 'string', 'max:10000', $plain],
            'parent_id' => ['nullable', 'integer', 'min:1']])->validate();
        if (! app(ContentPaths::class)->available($data['path'], $term->exists ? $term->id : null, $term->getTable())) {
            throw ValidationException::withMessages(['path' => 'Use a unique exact path without reserved routes or aliases.']);
        }
        if ($term->exists && $term->path !== $data['path']) {
            throw ValidationException::withMessages(['path' => 'Keep the saved term path. URL changes need a reviewed redirect workflow.']);
        }
        $data['parent_id'] = empty($data['parent_id']) ? null : (int) $data['parent_id'];
        if ($term instanceof Tag && $data['parent_id'] !== null) {
            throw ValidationException::withMessages(['parent_id' => 'Tags do not have parent terms.']);
        }
        if ($term instanceof Category) {
            $this->parent($term, $data['parent_id']);
        }
        $data['description'] ??= null;

        return $data;
    }

    private function parent(Category $term, ?int $parentId): void
    {
        $depth = 1;
        $seen = $term->exists ? [$term->id => true] : [];
        while ($parentId !== null) {
            $parent = Category::query()->find($parentId);
            if (! $parent || isset($seen[$parentId]) || ++$depth > 5) {
                throw ValidationException::withMessages(['parent_id' => 'Choose an active category without cycles or more than five levels.']);
            }
            $seen[$parentId] = true;
            $parentId = $parent->parent_id;
        }
        $frontier = $term->exists ? [$term->id] : [];
        while ($frontier !== []) {
            $frontier = Category::query()->whereIn('parent_id', $frontier)->pluck('id')->all();
            if ($frontier !== [] && ++$depth > 5) {
                throw ValidationException::withMessages(['parent_id' => 'This move would put descendant categories beyond five levels.']);
            }
        }
    }
}
