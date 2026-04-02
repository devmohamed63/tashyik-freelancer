<script setup>
  const { t } = useI18n();
  const { contact_info } = useLayoutStore();
  const loader = ref(false);
  const status = ref(null);
  const errors = ref({});
  const credentials = ref({
    subject: '',
    name: '',
    email: '',
    phone: '',
    message: '',
  });

  async function handleRequest() {
    if (loader.value) return;

    loader.value = true;

    try {
      await useApiFetch('/contact-requests', {
        method: 'POST',
        body: credentials.value,
      });

      credentials.value = {
        subject: '',
        name: '',
        email: '',
        phone: '',
        message: '',
      };

      errors.value = {};

      status.value = t('contact.message_sent');

      setTimeout(() => {
        status.value = null;
      }, 1300);
    } catch (error) {
      if (error.statusCode === 422) {
        errors.value = error.data.errors;
      } else {
        console.error('Failed to send request:', error);
      }
    }

    loader.value = false;
  }
</script>

<template>
  <section class="bg-gray-50">
    <div class="container px-4 flex flex-col gap-8 py-14 md:py-20">
      <div class="flex flex-col gap-3 text-center">
        <h3 v-text="$t('contact.title')" class="text-xl md:text-3xl font-medium text-gray-800"></h3>
        <span v-text="$t('contact.subtitle')" class="md:text-lg text-gray-500"></span>
      </div>
      <div class="flex flex-col md:grid grid-cols-2 gap-6">
        <!-- Contact form -->
        <div class="bg-white rounded-lg shadow p-6 h-full">
          <form @submit.prevent="handleRequest" class="flex flex-col gap-5">
            <!-- Name -->
            <div>
              <InputsLabel for="name" class="font-medium" :name="$t('contact.inputs.name.title')" />
              <InputsDefault v-model="credentials.name" id="name" :placeholder="$t('contact.inputs.name.placeholder')" required />
              <InputsError :message="errors?.name?.[0]" />
            </div>

            <div class="flex flex-col gap-5 md:grid grid-cols-2">
              <!-- Email -->
              <div>
                <InputsLabel for="email" class="font-medium" :name="$t('inputs.email')" />
                <InputsDefault v-model="credentials.email" id="email" type="email" />
                <InputsError :message="errors?.email?.[0]" />
              </div>

              <!-- Phone -->
              <div>
                <InputsLabel for="phone" class="font-medium" :name="$t('inputs.phone')" />
                <InputsDefault v-model="credentials.phone" id="phone" required />
                <InputsError :message="errors?.phone?.[0]" />
              </div>
            </div>

            <!-- Subject -->
            <div>
              <InputsLabel for="subject" class="font-medium" :name="$t('contact.inputs.subject.title')" />
              <InputsDefault v-model="credentials.subject" id="subject" :placeholder="$t('contact.inputs.subject.placeholder')" required />
              <InputsError :message="errors?.subject?.[0]" />
            </div>

            <!-- Message -->
            <div>
              <InputsLabel for="message" class="font-medium" :name="$t('contact.inputs.message.title')" />
              <textarea v-model="credentials.message" id="message" :placeholder="$t('contact.inputs.message.placeholder')" required rows="4" class="rounded-xl p-3 border-gray-200 w-full focus:ring-2 ring-brand-100 text-sm"></textarea>
              <InputsError :message="errors?.message?.[0]" />
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <ButtonsFilled class="md:w-fit" type="submit" :disabled="loader">
                {{ $t('contact.actions.send') }}
                <svg class="w-5 h-5 stroke-white" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M12.667 8H3.33366" stroke-linecap="round" stroke-linejoin="round" />
                  <path d="M8 3.33325L3.33333 7.99992L8 12.6666" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </ButtonsFilled>
              <span v-if="status" v-text="status" class="text-green-500 dark:text-green-400 text-[15px]"></span>
            </div>
          </form>
        </div>

        <div class="flex flex-col gap-6">
          <!-- Contact info -->
          <div class="flex flex-col gap-5">
            <!-- Whatsapp -->
            <div v-if="contact_info?.phone_number" class="bg-white rounded-lg shadow p-6 inline-flex gap-4">
              <svg class="w-12 h-12 p-3 rounded-lg bg-brand-50 fill-brand-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M0 23.9999L1.687 17.837C0.645998 16.033 0.0989998 13.988 0.0999998 11.891C0.103 5.33499 5.43799 0 11.993 0C15.174 0.000999998 18.16 1.24 20.406 3.48799C22.6509 5.73599 23.8869 8.72398 23.8859 11.902C23.8829 18.459 18.548 23.7939 11.993 23.7939C10.003 23.7929 8.04198 23.2939 6.30499 22.346L0 23.9999ZM6.59698 20.193C8.27298 21.188 9.87298 21.784 11.989 21.785C17.437 21.785 21.875 17.351 21.878 11.9C21.88 6.43799 17.463 2.01 11.997 2.008C6.54498 2.008 2.11 6.44199 2.108 11.892C2.107 14.117 2.75899 15.783 3.85399 17.526L2.85499 21.174L6.59698 20.193ZM17.984 14.729C17.91 14.605 17.712 14.531 17.414 14.382C17.117 14.233 15.656 13.514 15.383 13.415C15.111 13.316 14.913 13.266 14.714 13.564C14.516 13.861 13.946 14.531 13.773 14.729C13.6 14.927 13.426 14.952 13.129 14.803C12.832 14.654 11.874 14.341 10.739 13.328C9.85598 12.54 9.25898 11.567 9.08598 11.269C8.91298 10.972 9.06798 10.811 9.21598 10.663C9.34998 10.53 9.51298 10.316 9.66198 10.142C9.81298 9.96998 9.86198 9.84598 9.96198 9.64698C10.061 9.44898 10.012 9.27498 9.93698 9.12598C9.86198 8.97798 9.26798 7.51498 9.02098 6.91998C8.77898 6.34099 8.53398 6.41899 8.35198 6.40999L7.78198 6.39999C7.58398 6.39999 7.26198 6.47399 6.98998 6.77199C6.71798 7.06998 5.94999 7.78798 5.94999 9.25098C5.94999 10.714 7.01498 12.127 7.16298 12.325C7.31198 12.523 9.25798 15.525 12.239 16.812C12.948 17.118 13.502 17.301 13.933 17.438C14.645 17.664 15.293 17.632 15.805 17.556C16.376 17.471 17.563 16.837 17.811 16.143C18.059 15.448 18.059 14.853 17.984 14.729Z"
                />
              </svg>
              <div class="flex flex-col gap-1">
                <span v-text="$t('contact.info.whatsapp')" class="text-gray-500"></span>
                <span v-text="contact_info?.phone_number" class="text-gray-800 font-medium" dir="ltr"></span>
              </div>
            </div>

            <!-- Phone -->
            <div v-if="contact_info?.phone_number" class="bg-white rounded-lg shadow p-6 inline-flex gap-4">
              <svg class="w-12 h-12 p-3 rounded-lg bg-brand-50 stroke-brand-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_11625_4493)">
                  <path
                    d="M15.0504 5C16.0272 5.19057 16.9248 5.66826 17.6285 6.37194C18.3322 7.07561 18.8099 7.97326 19.0004 8.95M15.0504 1C17.0797 1.22544 18.972 2.13417 20.4167 3.57701C21.8613 5.01984 22.7724 6.91101 23.0004 8.94M22.0004 16.92V19.92C22.0016 20.1985 21.9445 20.4742 21.8329 20.7293C21.7214 20.9845 21.5577 21.2136 21.3525 21.4019C21.1473 21.5901 20.905 21.7335 20.6412 21.8227C20.3773 21.9119 20.0978 21.9451 19.8204 21.92C16.7433 21.5856 13.7874 20.5341 11.1904 18.85C8.77425 17.3147 6.72576 15.2662 5.19042 12.85C3.5004 10.2412 2.44866 7.27099 2.12042 4.18C2.09543 3.90347 2.1283 3.62476 2.21692 3.36162C2.30555 3.09849 2.44799 2.85669 2.63519 2.65162C2.82238 2.44655 3.05023 2.28271 3.30421 2.17052C3.5582 2.05833 3.83276 2.00026 4.11042 2H7.11042C7.59573 1.99522 8.06621 2.16708 8.43418 2.48353C8.80215 2.79999 9.0425 3.23945 9.11042 3.72C9.23704 4.68007 9.47187 5.62273 9.81042 6.53C9.94497 6.88792 9.97408 7.27691 9.89433 7.65088C9.81457 8.02485 9.62928 8.36811 9.36042 8.64L8.09042 9.91C9.51398 12.4135 11.5869 14.4864 14.0904 15.91L15.3604 14.64C15.6323 14.3711 15.9756 14.1858 16.3495 14.1061C16.7235 14.0263 17.1125 14.0555 17.4704 14.19C18.3777 14.5286 19.3204 14.7634 20.2804 14.89C20.7662 14.9585 21.2098 15.2032 21.527 15.5775C21.8441 15.9518 22.0126 16.4296 22.0004 16.92Z"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </g>
                <defs>
                  <clipPath id="clip0_11625_4493">
                    <rect width="24" height="24" fill="white" />
                  </clipPath>
                </defs>
              </svg>
              <div class="flex flex-col gap-1">
                <span v-text="$t('contact.info.phone')" class="text-gray-500"></span>
                <span v-text="contact_info?.phone_number" class="text-gray-800 font-medium" dir="ltr"></span>
              </div>
            </div>

            <!-- Email -->
            <div v-if="contact_info?.email" class="bg-white rounded-lg shadow p-6 inline-flex gap-4">
              <svg class="w-12 h-12 p-3 rounded-lg bg-brand-50 stroke-brand-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M22 6L12 13L2 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
              <div class="flex flex-col gap-1">
                <span v-text="$t('contact.info.email')" class="text-gray-500"></span>
                <span v-text="contact_info?.email" class="text-gray-800 font-medium"></span>
              </div>
            </div>
          </div>

          <!-- More info -->
          <div class="bg-brand-500 rounded-lg p-8 flex flex-col gap-5 text-center justify-center items-center">
            <svg class="w-12 h-12 p-3 rounded-lg bg-brand-50 text-brand-500 stroke-brand-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex flex-col gap-2">
              <span v-text="$t('contact.more_info.title')" class="text-xl text-white"></span>
              <span v-text="$t('contact.more_info.subtitle')" class="text-gray-100"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
