<template>
<div class="circular-counter html">
    <svg 
            width="48" 
            height="48" 
            xmlns="http://www.w3.org/2000/svg">
        <g>
            <circle     
                    id="circle" 
                    class="circle_animation" 
                    :style="{ 'stroke-dashoffset': strokeDashOffset}"
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
    data() {
        return {
            initialOffset: 100,
            i: -1,
            interval: null
        }
    },
    computed: {
        strokeDashOffset() {
            return this.initialOffset - ((this.i) * (this.initialOffset/this.time));
        }
    },
    props: {
        time: Number
    },
    created() {
        if (this.i == this.time) {  	
            this.initialOffset = 100;
        }
        this.i++; 

        this.interval = setInterval(() => {
            if (this.i == this.time) {  	
                this.initialOffset = 100;
            }
            this.i++;  
        }, 1000);
    },
    beforeDestroy() {
        clearInterval(this.interval);
    }
}
</script>

<style scoped>
.circular-counter {
    position: relative;
    float: left;
}

svg {
   -webkit-transform: rotate(-90deg);
    transform: rotate(-90deg);
}

.circle_animation {
    stroke-dasharray: 100;
    stroke-dashoffset: 100;
    transition: all 0.3s linear;
}
</style>

