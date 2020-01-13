<template>
<div class="circular-counter html">
    <svg 
            width="48" 
            height="48" 
            xmlns="http://www.w3.org/2000/svg">
        <g>
            <circle     
                    id="circle" 
                    class="circle-animation" 
                    :style="{ 'animation-duration': time + 's' }"
                    r="16" 
                    cy="28" 
                    cx="28" 
                    stroke-width="3" 
                    stroke="white" 
                    fill="none"/>
        </g>
    </svg>
</div>
</template>

<script>
export default {
    name: 'CircularCounter',
    props: {
        time: Number
    }
}
</script>

<style lang="scss" scoped>

.circular-counter {
    position: relative;
    float: left;
}

svg {
   -webkit-transform: rotate(-90deg);
    transform: rotate(-90deg);
}

@keyframes loading-counter {
    from {
        stroke-dashoffset: -100;
    }
    to {
        stroke-dashoffset: 0;
    }
}

.circle-animation {
    stroke-dasharray: 100;
    animation-name: loading-counter; 
    animation-iteration-count: infinite;
}
</style>

