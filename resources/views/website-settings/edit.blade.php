@extends('layouts.app')

@section('title', 'Website Settings')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-globe text-ec"></i> Website Settings</h1>
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right"></i> View website</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('website-settings.update') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Main text</div>
                <div class="card-body row g-3">
                    <div class="col-md-5"><label class="form-label" for="name">Name</label><input id="name" name="name" value="{{ old('name', $s['name']) }}" maxlength="60" class="form-control"></div>
                    <div class="col-md-7"><label class="form-label" for="tagline">Tagline</label><input id="tagline" name="tagline" value="{{ old('tagline', $s['tagline']) }}" maxlength="100" class="form-control"></div>
                    <div class="col-12"><label class="form-label" for="hero_title">Big headline on the home page</label><input id="hero_title" name="hero_title" value="{{ old('hero_title', $s['hero_title']) }}" maxlength="120" class="form-control"></div>
                    <div class="col-12"><label class="form-label" for="hero_text">Text under the headline</label><textarea id="hero_text" name="hero_text" rows="2" maxlength="300" class="form-control">{{ old('hero_text', $s['hero_text']) }}</textarea></div>
                    <div class="col-12"><label class="form-label" for="about_title">About heading</label><input id="about_title" name="about_title" value="{{ old('about_title', $s['about_title']) }}" maxlength="80" class="form-control"></div>
                    <div class="col-12"><label class="form-label" for="about_text">About text</label><textarea id="about_text" name="about_text" rows="6" maxlength="3000" class="form-control">{{ old('about_text', $s['about_text']) }}</textarea><div class="form-text">Leave an empty line between paragraphs.</div></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Contact details <span class="small text-muted fw-normal">(empty fields are hidden on the website)</span></div>
                <div class="card-body row g-3">
                    <div class="col-12"><label class="form-label" for="address">Address</label><input id="address" name="address" value="{{ old('address', $s['address']) }}" maxlength="255" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input id="phone" name="phone" value="{{ old('phone', $s['phone']) }}" maxlength="20" class="form-control" placeholder="077 123 4567"></div>
                    <div class="col-md-6"><label class="form-label" for="whatsapp">WhatsApp</label><input id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $s['whatsapp']) }}" maxlength="15" class="form-control" placeholder="94771234567"></div>
                    <div class="col-12"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', $s['email']) }}" maxlength="100" class="form-control"></div>
                    <div class="col-12">
                        <label class="form-label" for="map_embed">Google Maps embed link</label>
                        <input id="map_embed" name="map_embed" value="{{ old('map_embed', $s['map_embed']) }}" class="form-control" placeholder="https://www.google.com/maps/embed?pb=...">
                        <div class="form-text">Google Maps → find Elite Cafe → Share → Embed a map → copy only the link inside src="…".</div>
                    </div>
                    <div class="col-md-4"><label class="form-label" for="facebook">Facebook page</label><input id="facebook" name="facebook" value="{{ old('facebook', $s['facebook']) }}" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label" for="instagram">Instagram</label><input id="instagram" name="instagram" value="{{ old('instagram', $s['instagram']) }}" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label" for="tiktok">TikTok</label><input id="tiktok" name="tiktok" value="{{ old('tiktok', $s['tiktok']) }}" class="form-control"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Opening hours</div>
                <div class="card-body">
                    <p class="small text-muted">A closing time earlier than the opening time means after midnight. Example: open 08:00, close 02:00 = 8 AM to 2 AM.</p>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="copyMonday">Use Monday's hours for every day</button>
                    @foreach ($days as $key => $label)
                        @php $h = $s['hours'][$key] ?? ['open' => '08:00', 'close' => '22:00', 'closed' => false]; @endphp
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-3 small fw-semibold">{{ $label }}</div>
                            <div class="col-3"><input type="time" name="hours[{{ $key }}][open]" value="{{ old("hours.$key.open", $h['open']) }}" class="form-control form-control-sm" aria-label="{{ $label }} opens"></div>
                            <div class="col-3"><input type="time" name="hours[{{ $key }}][close]" value="{{ old("hours.$key.close", $h['close']) }}" class="form-control form-control-sm" aria-label="{{ $label }} closes"></div>
                            <div class="col-3 form-check small">
                                <input class="form-check-input" type="checkbox" name="hours[{{ $key }}][closed]" value="1" id="closed_{{ $key }}" @checked(old("hours.$key.closed", $h['closed'] ?? false))>
                                <label class="form-check-label" for="closed_{{ $key }}">Closed</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Photos</div>
                <div class="card-body">
                    @foreach (['hero_image' => 'Home page main photo (wide)', 'about_image' => 'About section photo (tall)'] as $field => $label)
                        <div class="mb-3">
                            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                            @if ($s[$field])
                                <img src="{{ asset('storage/'.$s[$field]) }}" alt="" class="d-block rounded mb-2" style="max-height: 120px;">
                                <div class="form-check small mb-1">
                                    <input class="form-check-input" type="checkbox" name="remove_{{ $field }}" value="1" id="remove_{{ $field }}">
                                    <label class="form-check-label" for="remove_{{ $field }}">Remove this photo</label>
                                </div>
                            @endif
                            <input type="file" id="{{ $field }}" name="{{ $field }}" accept="image/*" class="form-control">
                        </div>
                    @endforeach
                    <div class="form-text">Without a main photo, the home page shows your menu photos instead. Room photos are added in Rooms → Room Types.</div>
                </div>
            </div>

            <button class="btn btn-ec btn-lg w-100"><i class="bi bi-save"></i> Save website</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.getElementById('copyMonday').addEventListener('click', () => {
        const get = n => document.querySelector(`[name="hours[mon][${n}]"]`);
        ['tue', 'wed', 'thu', 'fri', 'sat', 'sun'].forEach(d => {
            document.querySelector(`[name="hours[${d}][open]"]`).value = get('open').value;
            document.querySelector(`[name="hours[${d}][close]"]`).value = get('close').value;
            document.querySelector(`[name="hours[${d}][closed]"]`).checked = get('closed').checked;
        });
    });
</script>
@endpush
