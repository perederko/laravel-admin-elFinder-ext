@php
    Encore\Admin\Facades\Admin::css('//ajax.googleapis.com/ajax/lis/jquery/1.10.4/themes/smoothness');
    Encore\Admin\Facades\Admin::css(asset($dir.'/css/elfinder.min.css'));
    Encore\Admin\Facades\Admin::css(asset($dir.'/css/theme.css'));
    Encore\Admin\Facades\Admin::css();
    Encore\Admin\Facades\Admin::js('//ajax.googleapis.com/ajax/lis/jquery/1.10.4/jquery-ui.min.js');
    Encore\Admin\Facades\Admin::js(asset($dir.'/js/elfinder.min.js'));
    if($locale){
        Encore\Admin\Facades\Admin::js(asset($dir."/js/i18n/elfinder.$locale.js"));
    }
@endphp

<!-- Element where elFinder will be created (REQUIRED) -->
<div id="elfinder"></div>
<script type="text/javascript" charset="utf-8">
    // Documentation for client options:
    // https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
    $().ready(function () {
        $('#elfinder').elfinder({
            // set your elFinder options here
            @if($locale)
            lang: '{{ $locale }}', // locale
            @endif
            customData: {
                _token: '{{ csrf_token() }}'
            },
            url: '{{ route("admin-elfinder.connector") }}',  // connector URL
            soundPath: '{{ asset($dir.'/sounds') }}'
        });
    });
</script>