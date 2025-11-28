<x-guest-layout>
  <x-nav />
  <main>
    <div class="relative flex content-center items-center justify-center pt-16 pb-32" style="min-height: 30vh;">
      <div class="absolute top-0 h-full w-full bg-cover bg-center"
        style='background-image: url("{{ asset('img/background.jpg') }}");'>
        <span id="blackOverlay" class="absolute h-full w-full bg-black opacity-50"></span>
      </div>
      <div class="container relative mx-auto">
        <div class="flex flex-wrap items-center">
          <div class="ml-auto mr-auto w-full px-4 text-center lg:w-6/12">
            <div class="pr-12">
              <h1 class="text-5xl font-semibold text-white">
                Generator Laporan
              </h1>
              <p class="mt-4 text-lg text-gray-300">
                Generate laporan Excel detail pengajuan ternak berdasarkan filter yang Anda pilih
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
        <div class="flex flex-wrap justify-center">
          <div class="w-full px-4 lg:w-10/12">
            <div class="relative mb-6 flex w-full min-w-0 flex-col break-words rounded-lg bg-white shadow-lg">
              <div class="flex-auto p-6">
                <h2 class="text-2xl font-semibold mb-4">Filter Laporan</h2>
                
                @if ($errors->any())
                  <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
                    <ul class="list-disc list-inside">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif

                <form 
                  action="{{ route('laporan.generate') }}" 
                  method="POST" 
                  class="space-y-6" 
                  x-data="{ 
                    loading: false,
                    checkDownload() {
                      this.loading = true;
                      const interval = setInterval(() => {
                        if (document.cookie.includes('download_started=true')) {
                          this.loading = false;
                          clearInterval(interval);
                          document.cookie = 'download_started=; Max-Age=-99999999; path=/';
                        }
                      }, 1000);
                    }
                  }" 
                  @submit="checkDownload()"
                >
                  @csrf
                  
                  <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                      <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Mulai <span class="text-red-500">*</span>
                      </label>
                      <input 
                        type="date" 
                        id="tanggal_mulai" 
                        name="tanggal_mulai" 
                        value="{{ old('tanggal_mulai', now()->startOfMonth()->format('Y-m-d')) }}"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                      >
                      <p class="mt-1 text-sm text-gray-500">Pilih tanggal mulai periode laporan</p>
                    </div>

                    <div>
                      <label for="tanggal_akhir" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Akhir <span class="text-red-500">*</span>
                      </label>
                      <input 
                        type="date" 
                        id="tanggal_akhir" 
                        name="tanggal_akhir" 
                        value="{{ old('tanggal_akhir', now()->endOfMonth()->format('Y-m-d')) }}"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                      >
                      <p class="mt-1 text-sm text-gray-500">Pilih tanggal akhir periode laporan</p>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Jenis Ternak
                    </label>
                    <div class="space-y-2">
                      @foreach($jenisTernak as $kategori => $items)
                        <div x-data="{ open: false }" class="rounded-lg border border-gray-200 bg-white overflow-hidden">
                          <button 
                            type="button" 
                            @click="open = !open" 
                            class="flex w-full items-center justify-between bg-gray-50 px-4 py-3 text-left transition hover:bg-gray-100 focus:outline-none"
                          >
                            <span class="font-semibold text-gray-700">{{ $kategori }}</span>
                            <span :class="open ? 'rotate-180' : ''" class="transition-transform duration-200">
                              <i class="fas fa-chevron-down text-gray-500"></i>
                            </span>
                          </button>
                          
                          <div 
                            x-show="open" 
                            x-collapse
                            class="border-t border-gray-200 p-4"
                            style="display: none;"
                          >
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                              @foreach($items as $item)
                                <label class="inline-flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded transition">
                                  <input 
                                    type="checkbox" 
                                    name="jenis_ternak_ids[]" 
                                    value="{{ $item['id'] }}" 
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    {{ in_array($item['id'], old('jenis_ternak_ids', [])) ? 'checked' : '' }}
                                  >
                                  <span class="text-sm text-gray-700">{{ $item['nama'] }}</span>
                                </label>
                              @endforeach
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                      Pilih jenis ternak yang ingin disertakan dalam laporan (kosongkan untuk memilih semua).
                    </p>
                  </div>

                  <div class="space-y-4">
                    <!-- Loading Message -->
                    <div x-show="loading" class="rounded-md bg-blue-50 p-4" style="display: none;" x-transition>
                      <div class="flex">
                        <div class="flex-shrink-0">
                          <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                          </svg>
                        </div>
                        <div class="ml-3 flex-1 md:flex md:justify-between">
                          <p class="text-sm text-blue-700">
                            Permintaan sedang diproses. Untuk periode laporan yang panjang (6 bulan - 1 tahun), proses ini mungkin memakan waktu hingga beberapa menit. Mohon jangan tutup halaman ini sampai download dimulai.
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="flex justify-end">
                      <button 
                        type="submit"
                        :disabled="loading"
                        :class="{ 'opacity-75 cursor-wait': loading }"
                        class="rounded-lg bg-green-500 px-6 py-3 text-white font-semibold hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-150 flex items-center"
                      >
                        <template x-if="!loading">
                          <div class="flex items-center">
                            <i class="fas fa-download mr-2"></i>
                            <span>Generate Laporan Excel</span>
                          </div>
                        </template>
                        <template x-if="loading">
                          <div class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memproses...</span>
                          </div>
                        </template>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <div class="relative mb-6 flex w-full min-w-0 flex-col break-words rounded-lg bg-blue-50 shadow-lg">
              <div class="flex-auto p-6">
                <h3 class="text-xl font-semibold text-blue-900 mb-3">
                  <i class="fas fa-info-circle mr-2"></i>
                  Informasi Laporan
                </h3>
                <ul class="space-y-2 text-sm text-blue-800 list-disc list-inside">
                  <li>Laporan akan berisi <strong>7 sheet</strong>: Ringkasan Eksekutif, Detail Transaksi, Antar Kab/Kota, Pengeluaran, Pemasukan, Analisis Kuota, dan Breakdown Perusahaan</li>
                  <li>Data yang ditampilkan berdasarkan rentang tanggal yang dipilih</li>
                  <li>Jika jenis ternak tidak dipilih, semua jenis ternak akan dimasukkan dalam laporan</li>
                  <li>File Excel akan otomatis terdownload setelah proses selesai</li>
                  <li>Laporan mencakup data detail pergerakan ternak antar kabupaten/kota dan ke luar daerah</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
</x-guest-layout>

