<div class="flex space-x-1 justify-center">
    <a href="{{ route('admin.User.audit', $uuid) }}" class="hover:bg-gray-200 rounded cursor-pointer px-1">
        <span>
           <i class="feather icon-activity"></i>
        </span>
    </a>

    <a href="{{ route('admin.User.show', $uuid) }}" class="hover:bg-gray-200 rounded cursor-pointer px-1">
        <span>
            <i class="feather icon-eye"></i>
        </span>
    </a>

    <a href="{{ route('admin.User.verification', $uuid) }}" class="hover:bg-gray-200 rounded cursor-pointer px-1">
        <span>
            <i class="feather icon-shield"></i>
        </span>
    </a>
</div>
