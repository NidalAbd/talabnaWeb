@extends('laratrust::panel.layout')

@section('title', "Role details")

@section('content')
  <div>
  </div>
  <div class="flex flex-col">
    <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-32">
      <div
        class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200 p-8"
      >
        <label class="flex justify-between w-4/12">
          <span class="text-gray-900 font-bold">{{('vendor\laratrust\panel\roles\show.name_code_') }}</span>
          <span class="ml-4 text-gray-800">{{$role->id</span> }}
        </label>

        <label class="flex justify-between w-4/12 my-4">
          <span class="text-gray-900 font-bold">{{('vendor\laratrust\panel\roles\show.display_name_') }}</span>
          <span class="ml-4 text-gray-800">{{$role->id</span> }}
        </label>

        <label class="flex justify-between w-4/12 my-4">
          <span class="text-gray-900 font-bold">{{('vendor\laratrust\panel\roles\show.description_') }}</span>
          <span class="ml-4 text-gray-800">{{$role->id</span> }}
        </label>
        <span class="text-gray-900 font-bold">{{('vendor\laratrust\panel\roles\show.permissions_') }}</span>
        <ul class="grid grid-cols-1 md:grid-cols-4 list-inside">
          @foreach ($role->permissions as $permission)
            <li class="text-gray-800 list-disc" >{{$permission->id</li> }}
          @endforeach
        </ul>
        <div class="flex justify-end">
          <a
            href="{{route("laratrust.roles.index")}}"
            class="text-blue-600 hover:text-blue-900"
          >
            Back
          </a>
        </div>
      </form>
    </div>
  </div>
@endsection






