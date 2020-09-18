<div class="dropdown-menu navbar-dropdown dropdown-menu-right" aria-labelledby="navbarDropdownLanguageLink">
    @foreach(array_keys(config('locale.languages')) as $lang)
        @if($lang != app()->getLocale())
            <small><a href="{{ '/lang/'.$lang }}" class="dropdown-item">@lang('menus.language-picker.langs.'.$lang)</a></small>
        @endif
    @endforeach
</div>
