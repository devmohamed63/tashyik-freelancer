<script setup>
  const currentYear = computed(() => new Date().getFullYear());
  const { description, social_links, mobile_app_links, contact_info } = useLayoutStore();
  const config = useRuntimeConfig();
</script>

<template>
  <footer class="dark mt-auto text-white bg-brand-950">
    <div class="container max-md:px-4 max-md:py-12 py-24 flex flex-col md:grid grid-cols-2 xl:grid-cols-4 w-full max-md:gap-10 gap-32">
      <div class="flex flex-col gap-6 items-start">
        <AppLogo class="h-12" />
        <p v-text="description" class="font-light text-brand-50"></p>
        <div class="flex items-center gap-3">
          <a v-if="social_links?.facebook" :href="social_links?.facebook" aria-label="Facebook" target="_blank" rel="nofollow" class="flex items-center justify-center w-9 h-9 bg-brand-100 text-brand-950 rounded-lg">
            <!-- basil:facebook-solid -->
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Basil by Craftwork - https://creativecommons.org/licenses/by/4.0/ -->
              <path fill="currentColor" d="M14.2 2.875A4.625 4.625 0 0 0 9.575 7.5v2.575H7.1c-.124 0-.225.1-.225.225v3.4c0 .124.1.225.225.225h2.475V20.9c0 .124.1.225.225.225h3.4c.124 0 .225-.1.225-.225v-6.975h2.497c.103 0 .193-.07.218-.17l.85-3.4a.225.225 0 0 0-.218-.28h-3.347V7.5a.775.775 0 0 1 .775-.775h2.6c.124 0 .225-.1.225-.225V3.1c0-.124-.1-.225-.225-.225z" />
            </svg>
          </a>
          <a v-if="social_links?.twitter" :href="social_links?.twitter" aria-label="Twitter" target="_blank" rel="nofollow" class="flex items-center justify-center w-9 h-9 bg-brand-100 text-brand-950 rounded-lg">
            <!-- bi:twitter-x -->
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 16 16">
              <!-- Icon from Bootstrap Icons by The Bootstrap Authors - https://github.com/twbs/icons/blob/main/LICENSE.md -->
              <path fill="currentColor" d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07l-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z" />
            </svg>
          </a>
          <a v-if="social_links?.instagram" :href="social_links?.instagram" aria-label="Instagram" target="_blank" rel="nofollow" class="flex items-center justify-center w-9 h-9 bg-brand-100 text-brand-950 rounded-lg">
            <!-- ri:instagram-line -->
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Remix Icon by Remix Design - https://github.com/Remix-Design/RemixIcon/blob/master/License -->
              <path
                fill="currentColor"
                d="M12.001 9a3 3 0 1 0 0 6a3 3 0 0 0 0-6m0-2a5 5 0 1 1 0 10a5 5 0 0 1 0-10m6.5-.25a1.25 1.25 0 0 1-2.5 0a1.25 1.25 0 0 1 2.5 0M12.001 4c-2.474 0-2.878.007-4.029.058c-.784.037-1.31.142-1.798.332a2.9 2.9 0 0 0-1.08.703a2.9 2.9 0 0 0-.704 1.08c-.19.49-.295 1.015-.331 1.798C4.007 9.075 4 9.461 4 12c0 2.475.007 2.878.058 4.029c.037.783.142 1.31.331 1.797c.17.435.37.748.702 1.08c.337.336.65.537 1.08.703c.494.191 1.02.297 1.8.333C9.075 19.994 9.461 20 12 20c2.475 0 2.878-.007 4.029-.058c.782-.037 1.308-.142 1.797-.331a2.9 2.9 0 0 0 1.08-.703c.337-.336.538-.649.704-1.08c.19-.492.296-1.018.332-1.8c.052-1.103.058-1.49.058-4.028c0-2.474-.007-2.878-.058-4.029c-.037-.782-.143-1.31-.332-1.798a2.9 2.9 0 0 0-.703-1.08a2.9 2.9 0 0 0-1.08-.704c-.49-.19-1.016-.295-1.798-.331C14.926 4.006 14.54 4 12 4m0-2c2.717 0 3.056.01 4.123.06c1.064.05 1.79.217 2.427.465c.66.254 1.216.598 1.772 1.153a4.9 4.9 0 0 1 1.153 1.772c.247.637.415 1.363.465 2.428c.047 1.066.06 1.405.06 4.122s-.01 3.056-.06 4.122s-.218 1.79-.465 2.428a4.9 4.9 0 0 1-1.153 1.772a4.9 4.9 0 0 1-1.772 1.153c-.637.247-1.363.415-2.427.465c-1.067.047-1.406.06-4.123.06s-3.056-.01-4.123-.06c-1.064-.05-1.789-.218-2.427-.465a4.9 4.9 0 0 1-1.772-1.153a4.9 4.9 0 0 1-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.012 15.056 2 14.717 2 12s.01-3.056.06-4.122s.217-1.79.465-2.428a4.9 4.9 0 0 1 1.153-1.772A4.9 4.9 0 0 1 5.45 2.525c.637-.248 1.362-.415 2.427-.465C8.945 2.013 9.284 2 12.001 2"
              />
            </svg>
          </a>
          <a v-if="social_links?.snapchat" :href="social_links?.snapchat" aria-label="Snapchat" target="_blank" rel="nofollow" class="flex items-center justify-center w-9 h-9 bg-brand-100 text-brand-950 rounded-lg">
            <!-- mingcute:snapchat-line -->
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from MingCute Icon by MingCute Design - https://github.com/Richard9394/MingCute/blob/main/LICENSE -->
              <g fill="none" fill-rule="evenodd">
                <path d="m12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                <path fill="currentColor" d="M12 2a6 6 0 0 0-6 6v1.875L5.1 9.2a1 1 0 1 0-1.2 1.6l1.866 1.4c-.444 1.168-1.527 2.39-3.28 3.443a1.01 1.01 0 0 0-.318 1.412C3.286 18.732 5.06 20 7 20c.677 0 1.203.43 1.724.809C9.456 21.34 10.423 22 12 22s2.543-.659 3.275-1.191C15.797 20.429 16.323 20 17 20c1.94 0 3.714-1.268 4.832-2.945a1.01 1.01 0 0 0-.318-1.412c-1.753-1.053-2.836-2.275-3.28-3.443l1.866-1.4a1 1 0 0 0-1.2-1.6l-.9.675V8a6 6 0 0 0-6-6M8 8a4 4 0 0 1 8 0v3c0 2.198 1.42 4.209 3.538 5.735C18.752 17.556 17.825 18 17 18c-1.378 0-2.264.728-2.9 1.191c-.644.468-1.177.809-2.1.809s-1.457-.341-2.1-.809C9.265 18.728 8.379 18 7 18c-.826 0-1.752-.444-2.538-1.265C6.58 15.209 8 13.199 8 11z" />
              </g>
            </svg>
          </a>
          <a v-if="social_links?.tiktok" :href="social_links?.tiktok" aria-label="TikTok" target="_blank" rel="nofollow" class="flex items-center justify-center w-9 h-9 bg-brand-100 text-brand-950 rounded-lg">
            <!-- fa7-brands:tiktok -->
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 640 640">
              <!-- Icon from Font Awesome Brands by Dave Gandy - https://creativecommons.org/licenses/by/4.0/ -->
              <path fill="currentColor" d="M544.5 273.9c-44 .1-87-13.6-122.8-39.2v178.7c0 33.1-10.1 65.4-29 92.6s-45.6 48-76.6 59.6s-64.8 13.5-96.9 5.3s-60.9-25.9-82.7-50.8s-35.3-56-39-88.9s2.9-66.1 18.6-95.2s40-52.7 69.6-67.7s62.9-20.5 95.7-16v89.9c-15-4.7-31.1-4.6-46 .4s-27.9 14.6-37 27.3s-14 28.1-13.9 43.9s5.2 31 14.5 43.7s22.4 22.1 37.4 26.9s31.1 4.8 46-.1s28-14.4 37.2-27.1s14.2-28.1 14.2-43.8V64h88c-.1 7.4.6 14.9 1.9 22.2c3.1 16.3 9.4 31.9 18.7 45.7s21.3 25.6 35.2 34.6c19.9 13.1 43.2 20.1 67 20.1V274z" />
            </svg>
          </a>
        </div>
      </div>

      <!-- Resources -->
      <div class="flex h-full w-[250px] flex-col gap-4">
        <h3 v-text="$t('footer.resources')" class="text-xl max-md:text-lg mb-2 sm:mb-0"></h3>
        <div class="flex flex-col gap-3 *:font-light *:text-brand-50 *:hover:text-brand-200">
          <NuxtLinkLocale :to="{ name: 'service-provider-register' }" class="static-color"> {{ $t('navigation.service_provider') }}</NuxtLinkLocale>
          <NuxtLinkLocale :to="{ name: 'about' }" class="static-color"> {{ $t('navigation.about') }}</NuxtLinkLocale>
          <NuxtLinkLocale :to="{ name: 'privacy-policy' }" class="static-color"> {{ $t('navigation.privacy_policy') }}</NuxtLinkLocale>
          <NuxtLinkLocale :to="{ name: 'terms-and-conditions' }" class="static-color"> {{ $t('navigation.terms_and_conditions') }}</NuxtLinkLocale>
        </div>
      </div>

      <!-- Download App -->
      <div class="flex h-full w-[250px] flex-col gap-4">
        <h3 v-text="$t('footer.download_app')" class="text-xl max-md:text-lg mb-2 sm:mb-0"></h3>
        <div class="flex flex-col gap-3 *:font-light *:text-brand-50 *:hover:text-white">
          <a v-if="mobile_app_links?.google_play" :href="mobile_app_links?.google_play" class="text-white hover:bg-brand-800 border border-white rounded-lg py-2 px-4 flex flex-row gap-3 items-center justify-center w-[10rem]" style="color: white !important">
            <div class="flex flex-col items-center tracking-wide">
              <span v-text="$t('download_app.get_it_on')" class="text-[9px] uppercase text-gray-100"></span>
              <span class="text-[14px] font-medium">Google Play</span>
            </div>
            <!-- logos:google-play-icon -->
            <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" width="28.95" height="32" viewBox="0 0 256 283">
              <!-- Icon from SVG Logos by Gil Barbara - https://raw.githubusercontent.com/gilbarbara/logos/master/LICENSE.txt -->
              <path fill="#EA4335" d="M119.553 134.916L1.06 259.061a32.14 32.14 0 0 0 47.062 19.071l133.327-75.934z" />
              <path fill="#FBBC04" d="M239.37 113.814L181.715 80.79l-64.898 56.95l65.162 64.28l57.216-32.67a31.345 31.345 0 0 0 0-55.537z" />
              <path fill="#4285F4" d="M1.06 23.487A30.6 30.6 0 0 0 0 31.61v219.327a32.3 32.3 0 0 0 1.06 8.124l122.555-120.966z" />
              <path fill="#34A853" d="m120.436 141.274l61.278-60.483L48.564 4.503A32.85 32.85 0 0 0 32.051 0C17.644-.028 4.978 9.534 1.06 23.399z" />
            </svg>
          </a>
          <a v-if="mobile_app_links?.app_store" :href="mobile_app_links?.app_store" class="text-white hover:bg-brand-800 border border-white rounded-lg py-2 px-4 flex flex-row gap-3 items-center justify-center w-[10rem]" style="color: white !important">
            <div class="flex flex-col items-center tracking-wide">
              <span v-text="$t('download_app.download_on_the')" class="text-[9px] uppercase text-gray-100"></span>
              <span class="text-[14px] font-medium">App Store</span>
            </div>
            <!-- teenyicons:apple-solid -->
            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 15 15">
              <!-- Icon from Teenyicons by smhmd - https://github.com/teenyicons/teenyicons/blob/master/LICENSE -->
              <path fill="currentColor" d="M7.875 2.937A.37.37 0 0 1 7.5 2.57C7.5 1.15 8.676 0 10.124 0a.37.37 0 0 1 .375.367c0 1.42-1.175 2.57-2.624 2.57" />
              <path fill="currentColor" d="M7.875 2.937A.37.37 0 0 1 7.5 2.57C7.5 1.15 8.676 0 10.124 0a.37.37 0 0 1 .375.367c0 1.42-1.175 2.57-2.624 2.57m5.475 7.731c.145.106.192.3.11.458C12.14 13.712 11.087 15 10.125 15c-.448 0-.888-.142-1.317-.418a1.99 1.99 0 0 0-2.073-.044c-.52.305-1.015.462-1.485.462c-1.415 0-3.75-4.267-3.75-6.608c0-2.5 1.339-4.406 3.375-4.406q1.437-.001 2.48.419c.294.118.627.11.914-.025c.564-.266 1.308-.394 2.23-.394c1.127 0 2.11.55 2.926 1.615a.36.36 0 0 1-.075.514c-.911.67-1.35 1.421-1.35 2.277c0 .855.439 1.607 1.35 2.276" />
              <path fill="currentColor" d="M13.35 10.668c.145.106.192.3.11.458C12.14 13.712 11.087 15 10.125 15c-.448 0-.888-.142-1.317-.418a1.99 1.99 0 0 0-2.073-.044c-.52.305-1.015.462-1.485.462c-1.415 0-3.75-4.267-3.75-6.608c0-2.5 1.339-4.406 3.375-4.406q1.437-.001 2.48.419c.294.118.627.11.914-.025c.564-.266 1.308-.394 2.23-.394c1.127 0 2.11.55 2.926 1.615a.36.36 0 0 1-.075.514c-.911.67-1.35 1.421-1.35 2.277c0 .855.439 1.607 1.35 2.276" />
            </svg>
          </a>
        </div>
      </div>

      <!-- Contact us -->
      <div class="flex flex-col gap-4">
        <h3 v-text="$t('footer.contact_us.title')" class="text-xl max-md:text-lg mb-2 sm:mb-0"></h3>
        <div class="inline-flex items-center gap-2 text-brand-50 font-light">
          <div v-if="contact_info?.phone_number" class="flex items-center justify-center w-9 h-9 bg-brand-100 text-brand-950 rounded-lg">
            <!-- material-symbols:phone-in-talk-outline-sharp -->
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Material Symbols by Google - https://github.com/google/material-design-icons/blob/master/LICENSE -->
              <path fill="currentColor" d="M19 12q0-2.925-2.037-4.962T12 5V3q1.875 0 3.513.713t2.85 1.924t1.925 2.85T21 12zm-4 0q0-1.25-.875-2.125T12 9V7q2.075 0 3.538 1.463T17 12zm4.95 9q-3.125 0-6.187-1.35T8.2 15.8t-3.85-5.55T3 4.05V3h5.9l.925 5.025l-2.85 2.875q.55.975 1.225 1.85t1.45 1.625q.725.725 1.588 1.388T13.1 17l2.9-2.9l5 1.025V21zM6.025 9l1.65-1.65L7.25 5H5.025q.125 1.125.375 2.113T6.025 9m8.95 8.95q1 .425 2.013.675T19 18.95v-2.2l-2.35-.475zm0 0" />
            </svg>
          </div>
          <span v-text="contact_info?.phone_number" dir="ltr"></span>
        </div>
        <div class="inline-flex items-center gap-2 text-brand-50 font-light">
          <div v-if="contact_info?.email" class="flex items-center justify-center w-9 h-9 bg-brand-100 text-brand-950 rounded-lg">
            <!-- ci:mail -->
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from coolicons by Kryston Schwarze - https://creativecommons.org/licenses/by/4.0/ -->
              <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 6l6.108 4.612l.002.002c.678.497 1.017.746 1.389.842a2 2 0 0 0 1.002 0c.372-.096.712-.345 1.392-.844c0 0 3.917-3.006 6.107-4.612M3 15.8V8.2c0-1.12 0-1.68.218-2.108c.192-.377.497-.682.874-.874C4.52 5 5.08 5 6.2 5h11.6c1.12 0 1.68 0 2.107.218c.377.192.683.497.875.874c.218.427.218.987.218 2.105v7.607c0 1.118 0 1.676-.218 2.104a2 2 0 0 1-.875.874c-.427.218-.986.218-2.104.218H6.197c-1.118 0-1.678 0-2.105-.218a2 2 0 0 1-.874-.874C3 17.48 3 16.92 3 15.8" />
            </svg>
          </div>
          <span v-text="contact_info?.email"></span>
        </div>
        <div class="inline-flex items-center gap-2 text-brand-50 font-light">
          <div class="flex items-center justify-center w-9 h-9 bg-brand-100 text-brand-950 rounded-lg">
            <!-- ic:baseline-access-time -->
            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
              <!-- Icon from Google Material Icons by Material Design Authors - https://github.com/material-icons/material-icons/blob/master/LICENSE -->
              <path fill="currentColor" d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2M12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8s-3.58 8-8 8" />
              <path fill="currentColor" d="M12.5 7H11v6l5.25 3.15l.75-1.23l-4.5-2.67z" />
            </svg>
          </div>
          <span v-text="$t('footer.contact_us.support')"></span>
        </div>
      </div>
    </div>

    <div class="font-light container max-md:px-4 max-sm:py-6 py-8 border-t border-gray-600 flex flex-col md:items-end justify-between gap-1 gap-y-8 sm:flex-row text-sm sm:text-base mx-auto leading-6 tracking-wide text-white">
      <!-- Saudi Business Center -->
      <a :href="`https://eauthenticate.saudibusiness.gov.sa/certificate-details/${config.public.saudiBusinessCenterCertificate}`" target="_blank" class="flex max-md:flex-col flex-row items-start md:items-end text-sm gap-3 static-color">
        <img class="h-11" src="/svg/saudi-business-center.svg" alt="saudi-business-center" />
        <span v-text="$t('footer.saudi_business_center')"></span>
      </a>

      <div class="flex max-lg:flex-col flex-row gap-x-2 gap-y-1">
        {{ $t('footer.copyright') }} &copy; {{ currentYear }} {{ $t('common.brand') }}.
        <div>
          {{ $t('footer.developed_by') }}
          <a href="https://www.trafyekbls.com/ar" target="_blank" rel="nofollow" class="text-brand-200 underline hover:text-brand-300 font-normal static-color">
            {{ $t('footer.developer') }}
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>
