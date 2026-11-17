@php
    $previousLayout = json_decode((string) old('case_study_json', ''), true);
    $caseLayout = is_array($previousLayout) && is_array($previousLayout['sections'] ?? null) ? $previousLayout : ($portfolio->details['case_study'] ?? null);
@endphp
<div x-data="portfolioLayout" data-layout="{{ json_encode($caseLayout) }}" class="form-stack">
    <input type="hidden" name="case_study_json" value="{{ json_encode($caseLayout) }}" :value="serialize()">
    <input type="hidden" name="blocks_json" value="[]">
    <h2>Case-study design and content</h2>
    <p class="helper">Edit every page section below. Changes to a published case study appear on the live page when saved. Images and their alt text are managed in the media library.</p>
    <div class="form-grid">
        <div class="field"><label for="case-design">Design family</label><select class="admin-input" id="case-design" x-model="layout.design"><option value="modern">Modern case study</option><option value="legacy">Classic profile</option><option value="industrial">Industrial portfolio</option></select></div>
        <div class="field"><label for="case-date">Display month</label><input class="admin-input" id="case-date" type="month" x-model="layout.display_date" required></div>
        <div class="field"><label for="case-thumbnail">Portfolio card thumbnail</label><select class="admin-input" id="case-thumbnail" x-model="layout.thumbnail_media_id"><option value="">No image</option>@foreach($images as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select></div>
        <template x-for="field in colors" :key="field.key"><div class="field"><label :for="'case-' + field.key" x-text="field.label"></label><input class="admin-input" type="color" :id="'case-' + field.key" x-model="layout[field.key]"></div></template>
        <div class="field"><label for="case-width">Content width (px)</label><input class="admin-input" id="case-width" type="number" x-model.number="layout.container_width" min="960" max="1440" required></div>
    </div>
    <template x-for="(section, index) in layout.sections" :key="section._key">
        <details class="page-section form-stack" open>
            <summary><strong x-text="(index + 1) + '. ' + section.title"></strong> <span x-text="'(' + section.type + ')'"></span></summary>
            <div class="form-stack" style="padding-top:16px">
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <button class="admin-button" type="button" @click="move(index, -1)" :disabled="index === 0" :aria-label="'Move section ' + (index + 1) + ' up'">Move up</button>
                    <button class="admin-button" type="button" @click="move(index, 1)" :disabled="index === layout.sections.length - 1" :aria-label="'Move section ' + (index + 1) + ' down'">Move down</button>
                    <button class="admin-button" type="button" @click="layout.sections.splice(index, 1)">Remove section</button>
                </div>
                <div class="form-grid">
                    <div class="field"><label :for="'case-title-' + section._key">Section heading</label><input class="admin-input" :id="'case-title-' + section._key" x-model="section.title" maxlength="255" required></div>
                    <div class="field"><label :for="'case-eyebrow-' + section._key">Eyebrow / small heading</label><input class="admin-input" :id="'case-eyebrow-' + section._key" x-model="section.eyebrow" maxlength="150"></div>
                    <div class="field"><label :for="'case-position-' + section._key">Image position (desktop)</label><select class="admin-input" :id="'case-position-' + section._key" x-model="section.image_position"><option value="left">Left</option><option value="right">Right</option></select></div>
                    <div class="field"><label :for="'case-image-' + section._key">Section image</label><select class="admin-input" :id="'case-image-' + section._key" x-model="section.image_id"><option value="">No image</option>@foreach($images as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select></div>
                    <div class="field"><label :for="'case-top-' + section._key">Top spacing (px)</label><input class="admin-input" :id="'case-top-' + section._key" type="number" x-model.number="section.padding_top" min="0" max="240" required></div>
                    <div class="field"><label :for="'case-bottom-' + section._key">Bottom spacing (px)</label><input class="admin-input" :id="'case-bottom-' + section._key" type="number" x-model.number="section.padding_bottom" min="0" max="240" required></div>
                    <div class="field"><label :for="'case-button-' + section._key">Button label</label><input class="admin-input" :id="'case-button-' + section._key" x-model="section.button_label" maxlength="100"></div>
                    <div class="field"><label :for="'case-url-' + section._key">Button URL</label><input class="admin-input" :id="'case-url-' + section._key" x-model="section.button_url" maxlength="1000" placeholder="/contact or https://example.com"></div>
                    <div class="field" x-show="section.type === 'hero'"><label :for="'case-badge-' + section._key">Growth badge value</label><input class="admin-input" :id="'case-badge-' + section._key" x-model="section.badge_value" maxlength="30"></div>
                    <div class="field" x-show="['hero', 'testimonial'].includes(section.type)"><label :for="'case-badge-label-' + section._key">Growth badge label / testimonial attribution</label><input class="admin-input" :id="'case-badge-label-' + section._key" x-model="section.badge_label" maxlength="100"></div>
                </div>
                <div class="field"><label :for="'case-heading-url-' + section._key">Heading link (optional)</label><input class="admin-input" :id="'case-heading-url-' + section._key" x-model="section.heading_url" maxlength="1000"></div>
                <div class="field"><label :for="'case-body-' + section._key">Section text</label><textarea class="admin-input" :id="'case-body-' + section._key" rows="7" maxlength="20000" x-model="section.body"></textarea><p class="helper">Paragraphs and basic formatting such as &lt;p&gt;, &lt;strong&gt; and &lt;a&gt; are supported. Unsafe HTML is removed.</p></div>
                <label class="checkbox-label"><input type="checkbox" x-model="section.visible"> Show this section</label>
                <label class="checkbox-label"><input type="checkbox" x-model="section.hide_title"> Hide section heading</label>
                <div class="field"><label :for="'case-image-url-' + section._key">Image link (optional)</label><input class="admin-input" :id="'case-image-url-' + section._key" x-model="section.image_url" maxlength="1000"></div>
                <div class="form-stack" x-show="['results', 'related', 'impact', 'services', 'gallery', 'columns'].includes(section.type)">
                    <h3>Cards (in display order)</h3>
                    <template x-for="(card, cardIndex) in section.cards" :key="cardIndex">
                        <fieldset class="form-grid page-section"><legend x-text="'Card ' + (cardIndex + 1)"></legend>
                            <div class="field"><label :for="'card-title-' + section._key + '-' + cardIndex">Value / project title</label><input class="admin-input" :id="'card-title-' + section._key + '-' + cardIndex" x-model="card.title" maxlength="255" required></div>
                            <div class="field"><label :for="'card-label-' + section._key + '-' + cardIndex">Label / display date</label><input class="admin-input" :id="'card-label-' + section._key + '-' + cardIndex" x-model="card.label" maxlength="150"></div>
                            <div class="field" x-show="['related', 'gallery'].includes(section.type)"><label :for="'card-url-' + section._key + '-' + cardIndex">Project URL</label><input class="admin-input" :id="'card-url-' + section._key + '-' + cardIndex" x-model="card.url" maxlength="1000"></div>
                            <div class="field" x-show="['related', 'gallery'].includes(section.type)"><label :for="'card-image-' + section._key + '-' + cardIndex">Card image</label><select class="admin-input" :id="'card-image-' + section._key + '-' + cardIndex" x-model="card.image_id"><option value="">No image</option>@foreach($images as $id => $label)<option value="{{ $id }}">{{ $label }}</option>@endforeach</select></div>
                            <div class="field" x-show="['services', 'gallery', 'columns'].includes(section.type)"><label :for="'card-body-' + section._key + '-' + cardIndex">Card description</label><textarea class="admin-input" :id="'card-body-' + section._key + '-' + cardIndex" x-model="card.body" rows="4" maxlength="5000"></textarea></div>
                            <div class="field" x-show="section.type === 'services'"><label :for="'card-icon-' + section._key + '-' + cardIndex">Card icon (emoji)</label><input class="admin-input" :id="'card-icon-' + section._key + '-' + cardIndex" x-model="card.icon" maxlength="16"></div>
                            <div style="display:flex;gap:10px;flex-wrap:wrap"><button type="button" class="admin-button" @click="moveCard(section, cardIndex, -1)" :disabled="cardIndex === 0">Move earlier</button><button type="button" class="admin-button" @click="moveCard(section, cardIndex, 1)" :disabled="cardIndex === section.cards.length - 1">Move later</button><button type="button" class="admin-button" @click="section.cards.splice(cardIndex, 1)">Remove card</button></div>
                        </fieldset>
                    </template>
                    <button class="admin-button" type="button" @click="section.cards.push({title: '', label: '', url: '', image_id: ''})" :disabled="section.cards.length >= 20">Add card</button>
                </div>
            </div>
        </details>
    </template>
    <div class="field"><label for="case-new-section">New section type</label><select class="admin-input" id="case-new-section" x-model="newType"><option value="hero">Hero banner</option><option value="report">Image and report text</option><option value="results">Results statistics</option><option value="related">Related projects carousel</option><option value="cta">Call to action</option><option value="intro">Classic introduction</option><option value="text">Text / services strip</option><option value="services">Service cards</option><option value="gallery">Results gallery</option><option value="impact">Business impact circles</option><option value="testimonial">Testimonial</option><option value="columns">Two text columns</option></select></div>
    <div><button class="admin-button" type="button" @click="add()" :disabled="layout.sections.length >= 30">Add section</button></div>
    <noscript><p>JavaScript is required to edit the layout. Saving retains the existing layout.</p></noscript>
</div>
