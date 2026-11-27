@extends('layouts.admin')
@section('title', 'Theme & Global Settings')

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl space-y-6">
    @csrf
    <div class="bg-white p-6 rounded-xl border border-slate-200 space-y-4">
        <h2 class="font-bold text-slate-800 border-b pb-2">1. Site Mode & Theme Control (30-Minute Delivery Setup)</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <label class="block font-semibold mb-1">Site Mode</label>
                <select name="site_mode" class="w-full px-3 py-2 border rounded-lg">
                    <option value="news" {{ ($settings['site_mode'] ?? 'news') === 'news' ? 'selected' : '' }}>News Portal</option>
                    <option value="business" {{ ($settings['site_mode'] ?? '') === 'business' ? 'selected' : '' }}>Corporate Business</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Active Frontend Theme</label>
                <select name="active_theme" class="w-full px-3 py-2 border rounded-lg">
                    <option value="news-classic" {{ ($settings['active_theme'] ?? 'news-classic') === 'news-classic' ? 'selected' : '' }}>News Classic (Default)</option>
                    <option value="corporate-clean" {{ ($settings['active_theme'] ?? '') === 'corporate-clean' ? 'selected' : '' }}>Corporate Clean</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold mb-1">Primary Brand Color</label>
                <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#dc2626' }}" class="w-full h-10 p-1 border rounded-lg">
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-slate-200 space-y-4 text-sm">
        <h2 class="font-bold text-slate-800 border-b pb-2">2. Branding, Editorial & Contact Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Website / Portal Name</label>
                <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'সিলেট প্রতিদিন ২৪' }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">Tagline / Slogan</label>
                <input type="text" name="site_tagline" value="{{ $settings['site_tagline'] ?? 'সত্য ও ন্যায়ের পথে অবিচল' }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">Editor & Publisher Name</label>
                <input type="text" name="editor_name" value="{{ $settings['editor_name'] ?? '' }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">WhatsApp / Contact Phone</label>
                <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '+8801700000000' }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Office Address</label>
                <input type="text" name="office_address" value="{{ $settings['office_address'] ?? 'বিশ্বনাথ, সিলেট, বাংলাদেশ' }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">Upload Logo</label>
                <input type="file" name="logo" accept="image/*" class="w-full border rounded-lg p-2">
                @if(!empty($settings['logo']))
                    <img src="{{ asset('storage/' . $settings['logo']) }}" class="h-12 mt-2">
                @endif
            </div>
            <div>
                <label class="block font-semibold mb-1">Upload Favicon</label>
                <input type="file" name="favicon" accept="image/*" class="w-full border rounded-lg p-2">
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-xl border border-slate-200 space-y-4 text-sm">
        <h2 class="font-bold text-slate-800 border-b pb-2">3. Global SEO, Analytics & Header/Footer Code Injection</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Facebook Pixel ID</label>
                <input type="text" name="fb_pixel_id" value="{{ $settings['fb_pixel_id'] ?? '' }}" placeholder="123456789012345" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block font-semibold mb-1">Google AdSense Publisher ID</label>
                <input type="text" name="adsense_client" value="{{ $settings['adsense_client'] ?? '' }}" placeholder="ca-pub-XXXXXXXXXXXXXXXX" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Default SEO Meta Description</label>
                <textarea name="default_meta_desc" rows="2" class="w-full px-3 py-2 border rounded-lg">{{ $settings['default_meta_desc'] ?? '' }}</textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1">Custom Header Code (&lt;head&gt; — Search Console / GA4)</label>
                <textarea name="custom_header_code" rows="4" placeholder="<meta name='google-site-verification' ...>" class="w-full px-3 py-2 border rounded-lg font-mono text-xs">{{ $settings['custom_header_code'] ?? '' }}</textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1">Custom Footer Code (Before &lt;/body&gt; — Chat / Scripts)</label>
                <textarea name="custom_footer_code" rows="4" placeholder="<script>...</script>" class="w-full px-3 py-2 border rounded-lg font-mono text-xs">{{ $settings['custom_footer_code'] ?? '' }}</textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow">
        Save All Settings
    </button>
</form>
@endsection
