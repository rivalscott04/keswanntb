<nav class="absolute top-0 z-50 flex w-full flex-wrap items-center justify-between px-2 py-3">
  <div class="container mx-auto flex flex-wrap items-center justify-between px-4">
    <div class="relative flex w-full justify-between lg:static lg:block lg:w-auto lg:justify-start">
      <a class="mr-4 inline-block whitespace-nowrap py-2 text-sm font-bold uppercase leading-relaxed text-white"
        href="{{ route('utama') }}">SIM LANTAS KWAN</a><button
        class="block cursor-pointer rounded border border-solid border-transparent bg-transparent px-3 py-1 text-xl leading-none outline-none focus:outline-none lg:hidden"
        type="button" onclick="toggleNavbar('example-collapse-navbar')">
        <i class="fas fa-bars text-white"></i>
      </button>
    </div>
    <div class="hidden flex-grow items-center bg-white lg:flex lg:bg-transparent lg:shadow-none"
      id="example-collapse-navbar">
      <ul class="mr-auto flex list-none flex-col lg:flex-row">
        <li class="flex items-center">
          <a class="flex items-center px-3 py-4 text-xs font-bold uppercase text-gray-800 lg:py-2 lg:text-white lg:hover:text-gray-300"
            href="#"><i
              class="far fa-file-alt leading-lg mr-2 text-lg text-gray-500 lg:text-gray-300"></i>
            Dokumen</a>
        </li>
      </ul>
      <ul class="flex list-none flex-col lg:ml-auto lg:flex-row">
        <li class="flex items-center">
          <a href="/admin/login"
            class="ml-3 mb-3 rounded bg-white px-4 py-2 text-xs font-bold uppercase text-gray-800 shadow outline-none hover:shadow-md focus:outline-none active:bg-gray-100 lg:mr-1 lg:mb-0"
            type="button" style="transition: all 0.15s ease 0s;">
            <i class="fas fa-arrow-alt-circle-down"></i> Masuk
          </a>
        </li>
        <li class="flex items-center">
          <a href="{{ route('daftar') }}"
            class="ml-3 mb-3 rounded bg-white px-4 py-2 text-xs font-bold uppercase text-gray-800 shadow outline-none hover:shadow-md focus:outline-none active:bg-gray-100 lg:mr-1 lg:mb-0"
            type="button" style="transition: all 0.15s ease 0s;">
            <i class="fas fa-arrow-alt-circle-down"></i> Daftar
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

