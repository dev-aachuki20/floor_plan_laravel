<div class="leftside-menu">

    <!-- Brand Logo Light -->
    <a href="javascript:void(0);" class="logo logo-light">
        <span class="logo-lg">
            <img src="{{ asset(config('constant.default.logo')) }}" alt="logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('backend/images/logo-sm.png') }}" alt="small logo">
        </span>
    </a>

    <!-- Brand Logo Dark -->
    <a href="javascript::void(0);" class="logo logo-dark">
        <span class="logo-lg">
            <img src="{{ asset('backend/images/logo-dark.png') }}" alt="dark logo">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('backend/images/logo-sm.png') }}" alt="small logo">
        </span>
    </a>

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!--- Sidemenu -->
        <ul class="side-nav">

            <li class="side-nav-item {{ request()->is('admin/dashboard') ? 'menuitem-active' : ''}}">
                <a href="{{ route('admin.dashboard') }}" class="side-nav-link {{ request()->is('admin/dashboard') ? 'active' : ''}}">
                    <i class="ri-dashboard-3-line"></i>
                    <span> Dashboard </span>
                </a>
            </li>

           
            @can('company_access')
            <li class="side-nav-item {{ request()->is('admin/companies*') ? 'menuitem-active' : ''}}">
                <a data-bs-toggle="collapse" href="#companyLayouts" aria-expanded="false" aria-controls="companyLayouts" class="side-nav-link">
                    <i class="ri-layout-line"></i>
                    <span> Companies </span>
                </a>
                <div class="collapse {{ request()->is('admin/companies*') ? 'show' : ''}}" id="companyLayouts">
                    <ul class="side-nav-second-level">
                        <li class="{{ request()->is('admin/companies*') && (!request()->is('admin/companies/create')) ? 'menuitem-active' : ''}}">
                            <a href="{{ route('admin.companies.index') }}" class="{{ request()->is('admin/companies*') && (!request()->is('admin/companies/create')) ? 'active' : ''}}">List</a>
                        </li>
                        <li class="{{ request()->is('admin/companies/create') ? 'menuitem-active' : ''}}">
                            <a href="{{ route('admin.companies.create') }}">Create</a>
                        </li>
                    </ul>
                </div>
            </li>
            @endcan


            @if (auth()->user()->can('service_access') && auth()->user()->can('industry_access'))

                <li class="side-nav-item {{ (request()->is('admin/services*') || request()->is('admin/industries*')) ? 'menuitem-active' : ''}}">
                    <a data-bs-toggle="collapse" href="#masterLayouts" aria-expanded="false" aria-controls="masterLayouts" class="side-nav-link">
                        <i class="ri-layout-line"></i>
                        <span> Master </span>
                    </a>
                    <div class="collapse {{ (request()->is('admin/services*') || request()->is('admin/industries*')) ? 'show' : ''}}" id="masterLayouts">
                        <ul class="side-nav-second-level">

                            <li class="{{ request()->is('admin/services*') ? 'menuitem-active' : ''}}">
                                <a href="{{ route('admin.services.index') }}" class="{{ request()->is('admin/services*') ? 'active' : ''}}">Services</a>
                            </li>

                            <li class="{{ request()->is('admin/industries*') ? 'menuitem-active' : ''}}">
                                <a href="{{ route('admin.industries.index') }}" class="{{ request()->is('admin/industries*') ? 'active' : ''}}">Industries</a>
                            </li>

                        </ul>
                    </div>
                </li>

            @endif

           
        </ul>
        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>