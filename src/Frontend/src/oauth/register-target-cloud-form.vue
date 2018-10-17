<template>
    <div>
        <register-slider :texts="slides"></register-slider>
        <div class="create-form">
            <div class="wrapper">
                <h1 class="page-title"
                    v-title>
                    {{ $t('Register SUPLA Cloud') }}</h1>
                <form @submit.prevent="submit()"
                    class="register-form">

                    <input type="email"
                        class="form-input"
                        autocorrect="off"
                        autocapitalize="none"
                        :placeholder="$t('Enter your email address')"
                        v-model="username">
                    <span class="help-block">Użyjemy go wyłącznie w uzasadnionych przypadkach.</span>


                    <input type="text"
                        class="form-input"
                        autocorrect="off"
                        autocapitalize="none"
                        :placeholder="$t('Where is your SUPLA Cloud?')"
                        v-model="username">
                    <span class="help-block">Podaj tylko domenę lub adres IP, razem z portem jeśli nie jest standardowy (443). Wymagamy połączenia HTTPS.</span>

                    <button type="submit"
                        class="btn btn-default btn-block btn-lg">
                        <span v-if="!isBusy">
                            {{ $t('Register') }}
                        </span>
                        <button-loading-dots v-else></button-loading-dots>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
    import RegisterSlider from '../register/register-slider';

    export default {
        components: {RegisterSlider},
        data() {
            return {
                slides: [
                    {
                        img: 'assets/img/1.svg',
                        title: 'Jeszcze większa funkcjonalność',
                        description: 'Dzięki rejestracji Twojej instancji SUPLA Cloud możliwe będzie skorzystanie z funkcjonalności dostaraczanych przez zewnętrzne aplikacje. Nie wiesz jakie? Sprawdź w katalogu!'
                    },
                    {
                        img: 'assets/img/2.svg',
                        title: 'SUPLA everywhere!',
                        description: 'One of the main assumptions of this project is simplicity and availability. SUPLA allows simple light operation or gate opening, when you want to leave your premises, with just touch of a finger. SUPLA is available from any place on Earth if you just have a smartphone or tables available as well as Internet access.  SUPLA is developed based on an Open Software and Hardware. This way, you can also develop this project!.'
                    },
                    {
                        img: 'assets/img/3.svg',
                        title: 'Free of charge and open!',
                        description: 'Building automation systems available on the market are usually very complex, closed and expensive. In many cases they must be installed on the very early stages of house construction.  SUPLA is simple, open and free of charge. It gives an opportunity to build elements based on RaspberryPI, Arduino or ESP8266 platforms and then join them either through LAN or WiFi. Join the project and build your own version of building automation system!'
                    },
                ]
            }
        }
    };
</script>
