<template>
    <div class="loader-dots">
        <div></div>
        <div></div>
        <div></div>
    </div>
</template>

<style lang="scss">
    .loader-dots {
        font-size: 0;
        display: block;
        position: relative;
        width: 120px;
        margin: 100px auto;
        height: 1px;
    }

    .loader-dots div {
        background: none;
        border: solid 2px black;
        width: 25px;
        height: 25px;
        border-radius: 30px;
        position: absolute;
        animation: dots-bounce 1.75s infinite;
        top: 50%;
        margin-top: -15px;
    }

    .loader-dots div:nth-child(2) {
        animation-delay: 0.25s;
        left: 50%;
        margin-left: -15px;
    }

    .loader-dots div:nth-child(3) {
        animation-delay: 0.5s;
        right: 0;
    }

    @keyframes dots-bounce {
        10% {
            transform: translate3d(0, 30px, 0);
            animation-timing-function: cubic-bezier(0.5, 0, 0.5, 1);
            height: 30px;
        }
        14% {
            height: 90px;
        }
        15% {
            height: 30px;
            transform: translate3d(0, -60px, 0);
            animation-timing-function: cubic-bezier(0.8, 0, 0.8, 1);
        }
        35% {
            transform: translate3d(0, 0, 0);
        }
    }

    .black .loader-dots div {
        border-color: white;
    }
</style>
