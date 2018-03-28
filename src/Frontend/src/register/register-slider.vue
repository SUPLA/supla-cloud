<template>
<div id="slides">
    <ul class="slides-container">
        <li v-for="slide in slides">
            <img :src="slide.img" alt="">
            <div class="container">
                <div class="info-wrapper">
                    <article>
                        <h1>{{ $t(slide.title) }}</h1>
                        <p>{{ $t(slide.description) }}</p>
                    </article>
                </div>
            </div>
        </li>
    </ul>
    <nav class="slides-navigation">
        <a class="next"><i class="pe-7s-angle-right-circle"></i></a>
        <a class="prev"><i class="pe-7s-angle-left-circle"></i></a>
    </nav>
</div>
</template>
<script>

export default {

    data() {
        return {
            slides: [
                {
                    img: 'assets/img/1.svg',
                    title: 'Develop your own building automation',
                    description: 'Based on available electronics, SUPLA enables you to build simple modules to navigate and operate building automation elements. Through SUPLA you can, among others, control the lighting, switch on and off household appliances and media, open and shut gates and doors, or control room temperature. All the above can be done with just touch of a finger.' },
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
        };
    },
    mounted() {
        $('#slides').superslides({
            animation: 'fade',
            play: 12500,
            pagination: false,
        });
    }
};
</script>
<style lang="scss">

#slides {
    position: relative;
    width: 100%;
    overflow: visible;

    @media screen and (max-width: 899px) {
        display: none;
    }

    .slides-container {
        display: none;
    }

    .slides-navigation {
        margin: 0 auto;
        position: absolute;
        z-index: 3;
        top: 46%;
        width: 100%;

        a {
            position: absolute;
            display: block;
            font-size: 60px;
            color: rgba(255,255,255,0.7);
            padding: 0px;
            background: transparent;
            transition: all 0.3s;

            &:active,
            &:focus,
            &:hover {
                color: #fff;
                background: transparent;
            }

            &.prev {
                left: 20px;
            }
            &.next {
                right: 20px;
            }
        }
    }

    .container {
        height: 100%;
        display: table;
        width: auto;

        .info-wrapper {
            display: table-cell;
            height: 100%;
            vertical-align: middle;

            article {
                margin-left: 90px;
                width: 40%;

                h1 {
                    color: #fff;
                    text-transform: none;
                }

                p {
                    color: #fff;
                }
            }
        }
    }
}

</style>
