<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Elementor's generated crops were not exported. Retain the same images using their imported originals.
        $paths = [
            'Ecommerce-r75ggofi63po0d9r0uvzjr7r0da8ep0pn4kiy6qfd0.png' => '2025/06/Ecommerce.png',
            'healthcare-clinic-r75gh3gx7ga964nwl1e0nnf4ij83tuof170amm44lg.jpg' => '2025/06/healthcare-clinic.jpg',
            'saas-r75ghgmnv4s9oo4sg72smk3ktxf8tm4nr053chkm6c.webp' => '2025/06/saas.webp',
            'Education-edtech-r75ggyrq9a3tk2uqchcvt6ltjlv9rd5rcjqv88b3gk.png' => '2025/06/Education-edtech.png',
            'Custom-SEO-Strategy-The-Dm-School-rav33ib44ny8vpjpxl7ep670hb5q1julqjsxqstq9w.webp' => '2025/08/Custom-SEO-Strategy-The-Dm-School.webp',
            'create-transparent-png-rav39qyji2hryihehsb8r3i6geeg57mg9fpzfxkmys.png' => '2025/08/create-transparent-png.png',
            '10782922_19199749-scaled-r34lk9rj74q2okvks9gxhvu95o51trhum6ls3bdk5g.jpg' => '2025/03/10782922_19199749-scaled.jpg',
            'proven-results-label-sign-vector-260nw-2556554941-rav3q596q0xuhmnyqvgy8yjnidkyf4pvulewtp9ec6.jpg' => '2025/08/proven-results-label-sign-vector-260nw-2556554941.jpg',
        ];
        DB::transaction(function () use ($paths): void {
            foreach (DB::table('posts')->where('content_kind', 'article')->where('body', 'like', '%/uploads/elementor/thumbs/%')->get() as $post) {
                $body = $post->body;
                foreach ($paths as $old => $original) {
                    $body = str_replace('/wp-content/uploads/elementor/thumbs/'.$old, '/wp-content/uploads/'.$original, $body);
                }
                if ($body !== $post->body) {
                    DB::table('posts')->where('id', $post->id)->update(['body' => $body,
                        'content_version' => $post->content_version + 1, 'updated_at' => now()]);
                }
            }
        });
    }

    public function down(): void
    {
        // Preserve corrected image references on rollback rather than reintroducing broken URLs.
    }
};
