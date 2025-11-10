<x-guest-layout>
  <x-nav />
  <main>
    <div class="relative flex content-center items-center justify-center pt-16 pb-32" style="min-height: 75vh;">
      <div class="absolute top-0 h-full w-full bg-cover bg-center"
        style='background-image: url("{{ asset('img/background.jpg') }}");'>
        <span id="blackOverlay" class="absolute h-full w-full bg-black opacity-50"></span>
      </div>
      <div class="container relative mx-auto">
        <div class="flex flex-wrap items-center">
          <div class="ml-auto mr-auto w-full px-4 text-center lg:w-6/12">
            <div class="pr-12">
              <h1 class="text-5xl font-semibold text-white">
                SIM LANTAS KWAN
              </h1>
              <p class="mt-4 text-lg text-gray-300">
                Sistem Informasi Lalu Lintas Tata Niaga Peternakan adalah aplikasi untuk melihat, me-review, memverifikasi dan menampilkan laporan lalu lintas ternak yang ada di Provinsi NTB.
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="pointer-events-none absolute top-auto bottom-0 left-0 right-0 w-full overflow-hidden"
        style="height: 70px;">
        <svg class="absolute bottom-0 overflow-hidden" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
          version="1.1" viewBox="0 0 2560 100" x="0" y="0">
          <polygon class="fill-current text-gray-300" points="2560 0 2560 100 0 100"></polygon>
        </svg>
      </div>
    </div>
    <section class="-mt-24 bg-gray-300 pb-20">
      <div class="container mx-auto px-4">
        <div class="flex flex-wrap">
          <div class="w-full px-4 pt-6 text-center md:w-4/12 lg:pt-12">
            <div class="relative mb-8 flex w-full min-w-0 flex-col break-words rounded-lg bg-white shadow-lg">
              <div class="flex-auto px-4 py-5">
                <div
                  class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-full bg-red-400 p-3 text-center text-white shadow-lg">
                  <i class="fas fa-award"></i>
                </div>
                <h6 class="text-xl font-semibold">Cepat</h6>
                <p class="mt-2 mb-4 text-gray-600">
                  Aplikasi ini bisa diakses di mana saja dan kapan saja.
                </p>
              </div>
            </div>
          </div>
          <div class="w-full px-4 text-center md:w-4/12">
            <div class="relative mb-8 flex w-full min-w-0 flex-col break-words rounded-lg bg-white shadow-lg">
              <div class="flex-auto px-4 py-5">
                <div
                  class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-full bg-blue-400 p-3 text-center text-white shadow-lg">
                  <i class="fas fa-retweet"></i>
                </div>
                <h6 class="text-xl font-semibold">Mudah</h6>
                <p class="mt-2 mb-4 text-gray-600">
                    SIM LANTAS KWAN dapat diakses melalui komputer dan bahkan HP dengan data yang realtime.
                </p>
              </div>
            </div>
          </div>
          <div class="w-full px-4 pt-6 text-center md:w-4/12">
            <div class="relative mb-8 flex w-full min-w-0 flex-col break-words rounded-lg bg-white shadow-lg">
              <div class="flex-auto px-4 py-5">
                <div
                  class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-400 p-3 text-center text-white shadow-lg">
                  <i class="fas fa-fingerprint"></i>
                </div>
                <h6 class="text-xl font-semibold">Dimana Saja</h6>
                <p class="mt-2 mb-4 text-gray-600">
                  Asal perangkat Anda memiliki internet, Anda bisa mengecek lalu lintas ternak dengan mudah.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-32 flex flex-wrap items-center">
          <div class="mr-auto ml-auto w-full px-4 md:w-5/12">
            <div
              class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 p-3 text-center text-gray-600 shadow-lg">
              <i class="fas fa-user-friends text-xl"></i>
            </div>
            <h3 class="mb-2 text-3xl font-semibold leading-normal">
              Pengajuan Sertifikat Pendaftaran Perusahaan Peternakan (SP3)
            </h3>
            <p class="mt-4 mb-4 text-lg font-light leading-relaxed text-gray-700">
              Pengajuan SP3 saat ini lebih mudah karena Anda bisa mendaftar langsung dari aplikasi SIM LANTAS KWAN. Tinggal klik Daftar dan isikan informasi yang dibutuhkan
            </p>
            <a href="{{ route('daftar') }}"
              class="mt-8 font-bold text-gray-800">Yuk Ajukan SP3 Sekarang!</a>
          </div>
          <div class="mr-auto ml-auto w-full px-4 md:w-4/12">
            <div class="relative mb-6 flex w-full min-w-0 flex-col break-words rounded-lg bg-white shadow-lg">
              <img alt="..." class="max-w-full rounded-lg shadow-lg"
                src="{{ asset('img/dokumen.jpg') }}" />
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="relative py-20">
      <div class="pointer-events-none absolute bottom-auto top-0 left-0 right-0 -mt-20 w-full overflow-hidden"
        style="height: 80px;">
        <svg class="absolute bottom-0 overflow-hidden" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
          version="1.1" viewBox="0 0 2560 100" x="0" y="0">
          <polygon class="fill-current text-white" points="2560 0 2560 100 0 100"></polygon>
        </svg>
      </div>
      <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center">
          <div class="ml-auto mr-auto w-full px-4 md:w-4/12">
            <img alt="..." class="max-w-full rounded-lg shadow-lg"
              src="{{ asset('img/ternak.webp') }}" />
          </div>
          <div class="ml-auto mr-auto w-full px-4 md:w-5/12">
            <div class="md:pr-12">
              <div
                class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-pink-300 p-3 text-center text-pink-600 shadow-lg">
                <i class="fas fa-rocket text-xl"></i>
              </div>
              <h3 class="text-3xl font-semibold">Pengajuan rekomendasi & izin lalu lintas ternak dan produk ternak</h3>
              <p class="mt-4 text-lg leading-relaxed text-gray-600">
                Perusahaan dapat mengajukan SP3 dan mengajukan ternak dengan mudah secara online. Kemudian akan diproses oleh dinas terkait setiap tahapnya dan perusahaan dapat melihat status pengajuan lalu lintas ternaknya.
              </p>
              <ul class="mt-6 list-none">
                <li class="py-2">
                  <div class="flex items-center">
                    <div>
                      <span
                        class="mr-3 inline-block rounded-full bg-pink-200 py-1 px-2 text-xs font-semibold uppercase text-pink-600"><i
                          class="fas fa-fingerprint"></i></span>
                    </div>
                    <div>
                      <h4 class="text-gray-600">
                        Pengajuan SP3
                      </h4>
                    </div>
                  </div>
                </li>
                <li class="py-2">
                  <div class="flex items-center">
                    <div>
                      <span
                        class="mr-3 inline-block rounded-full bg-pink-200 py-1 px-2 text-xs font-semibold uppercase text-pink-600"><i
                          class="fab fa-html5"></i></span>
                    </div>
                    <div>
                      <h4 class="text-gray-600">Mengajukan rekomendasi dan izin lalu lintas ternak dan produk ternak</h4>
                    </div>
                  </div>
                </li>
                <li class="py-2">
                  <div class="flex items-center">
                    <div>
                      <span
                        class="mr-3 inline-block rounded-full bg-pink-200 py-1 px-2 text-xs font-semibold uppercase text-pink-600"><i
                          class="far fa-paper-plane"></i></span>
                    </div>
                    <div>
                      <h4 class="text-gray-600">Diverifikasi oleh dinas terkait</h4>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <footer class="relative bg-gray-300 pt-8 pb-6">
    <div class="pointer-events-none absolute bottom-auto top-0 left-0 right-0 -mt-20 w-full overflow-hidden"
      style="height: 80px;">
      <svg class="absolute bottom-0 overflow-hidden" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
        version="1.1" viewBox="0 0 2560 100" x="0" y="0">
        <polygon class="fill-current text-gray-300" points="2560 0 2560 100 0 100"></polygon>
      </svg>
    </div>
    <div class="container mx-auto px-4">
      <div class="flex flex-wrap">
        <div class="w-full px-4 lg:w-6/12">
          <h4 class="text-3xl font-semibold">Yuk kunjungi jejaring sosial kami!</h4>
          <h5 class="mt-0 mb-2 text-lg text-gray-700">
            Ada tanggapan maupun kritik dan saran terhadap layanan kami? Silakan kunjungi jejaring sosial kami di bawah ini.
          </h5>
          <div class="mt-6">
            <a href="https://twitter.com/disnakkeswanNTB" target="_blank"
              class="align-center mr-2 h-10 w-10 items-center justify-center rounded-full bg-white p-3 font-normal text-blue-400 shadow-lg outline-none focus:outline-none">
              <i class="fab fa-twitter flex"></i></a>
              <a href="https://www.facebook.com/DisnakkeswanProvNTB" target="_blank"
              class="align-center mr-2 h-10 w-10 items-center justify-center rounded-full bg-white p-3 font-normal text-blue-600 shadow-lg outline-none focus:outline-none">
              <i class="fab fa-facebook-square flex"></i></a>
              <a href="https://instagram.com/disnakkeswanntb" target="_blank"
              class="align-center mr-2 h-10 w-10 items-center justify-center rounded-full bg-white p-3 font-normal text-pink-400 shadow-lg outline-none focus:outline-none"
              type="button">
              <i class="fab fa-instagram flex"></i></a>
              <a href="https://www.youtube.com/channel/UCXKtfvDdDmKU-k4hCaTAdbg" target="_blank"
              class="align-center mr-2 h-10 w-10 items-center justify-center rounded-full bg-white p-3 font-normal text-gray-900 shadow-lg outline-none focus:outline-none"
              type="button">
              <i class="fab fa-youtube flex"></i>
            </a>
          </div>
        </div>
        <div class="w-full px-4 lg:w-6/12">
          <div class="items-top mb-6 flex flex-wrap">
            <div class="ml-auto w-full px-4 lg:w-4/12">
              <span class="mb-2 block text-sm font-semibold uppercase text-gray-600">Tautan Lain</span>
              <ul class="list-unstyled">
                <li>
                  <a class="block pb-2 text-sm font-semibold text-gray-700 hover:text-gray-900"
                    href="https://disnakkeswan.ntbprov.go.id/profil/struktur-organisasi/">Tentang Kami</a>
                </li>
                <li>
                  <a class="block pb-2 text-sm font-semibold text-gray-700 hover:text-gray-900"
                    href="https://disnakkeswan.ntbprov.go.id">Situs Utama</a>
                </li>
                <li>
                  <a class="block pb-2 text-sm font-semibold text-gray-700 hover:text-gray-900"
                    href="https://disnakkeswan.ntbprov.go.id/ppid/dip-disnakkeswan/">PPID</a>
                </li>
                <li>
                  <a class="block pb-2 text-sm font-semibold text-gray-700 hover:text-gray-900"
                    href="https://disnakkeswan.ntbprov.go.id/hubungi-kami-2/">Hubungi Kami</a>
                </li>
              </ul>
            </div>
            {{-- <div class="w-full px-4 lg:w-4/12">
              <span class="mb-2 block text-sm font-semibold uppercase text-gray-600">Other Resources</span>
              <ul class="list-unstyled">
                <li>
                  <a class="block pb-2 text-sm font-semibold text-gray-700 hover:text-gray-900"
                    href="https://github.com/creativetimofficial/argon-design-system/blob/master/LICENSE.md">MIT
                    License</a>
                </li>
                <li>
                  <a class="block pb-2 text-sm font-semibold text-gray-700 hover:text-gray-900"
                    href="https://creative-tim.com/terms">Terms &amp; Conditions</a>
                </li>
                <li>
                  <a class="block pb-2 text-sm font-semibold text-gray-700 hover:text-gray-900"
                    href="https://creative-tim.com/privacy">Privacy Policy</a>
                </li>
                <li>
                  <a class="block pb-2 text-sm font-semibold text-gray-700 hover:text-gray-900"
                    href="https://creative-tim.com/contact-us">Contact Us</a>
                </li>
              </ul>
            </div> --}}
          </div>
        </div>
      </div>
      <hr class="my-6 border-gray-400" />
      <div class="flex flex-wrap items-center justify-center md:justify-between">
        <div class="mx-auto w-full px-4 text-center md:w-4/12">
          <div class="py-1 text-sm font-semibold text-gray-600">
            Hak Cipta © {{ date('Y') }} SIM LANTAS KWAN oleh Nurul Huda
          </div>
        </div>
      </div>
    </div>

  </footer>
  @push('scripts')
  <script>
    function toggleNavbar(collapseID) {
              document.getElementById(collapseID).classList.toggle("hidden");
              document.getElementById(collapseID).classList.toggle("block");
            }
  </script>
  @endpush
</x-guest-layout>
