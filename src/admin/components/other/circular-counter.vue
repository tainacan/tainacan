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
                    :style="{ 'stroke-dashoffset': initialOffset }"
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
            initialOffset: 0,
            i: -1,
            interval: null
        }
    },
    computed: {
        strokeDashOffset() {
            return this.initialOffset;
        }
    },
    props: {
        time: Number,
        index: Number
    },
    mounted() {
        // if (this.i == this.time) {  	
        //     this.initialOffset = 100;
        // }
        // this.i++; 

        // this.interval = setInterval(() => {
        //     if (this.i == this.time) {  	
        //         this.initialOffset = 100;
        //     }
        //     this.i++;  
        // }, 1000);        
        this.initialOffset = 200;
        this.interval  = setInterval(() => {
            this.initialOffset -= 40;
            if (this.initialOffset <= -40)
                this.initialOffset = 200;
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
    stroke-dashoffset: 200;
    transition: all 0.1s linear;
}
</style>

