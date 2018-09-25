<template>

<div class="item html">
    <svg 
            width="42" 
            height="42" 
            xmlns="http://www.w3.org/2000/svg">
        <g>
            <title>Layer 1</title>
            <circle     
                    id="circle" 
                    class="circle_animation" 
                    :style="{ 'stroke-dashoffset': initialOffset-((i+1)*(initialOffset/time))}"
                    r="9" 
                    cy="12" 
                    cx="12" 
                    stroke-width="2" 
                    stroke="#6fdb6f" 
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
            initialOffset: '120',
            i: 1,
            interval: null
        }
    },
    props: {
        time: Number
    },
    created() {
        this.interval = setInterval(() => {
            if (this.i == this.time) {  	
                clearInterval(this.interval);
                return;
            }
            this.i++;  
        }, 1000)
    }
}
</script>

<style scoped>
.item {
    position: relative;
    float: left;
}

.item h2 {
    text-align:center;
    position: absolute;
    line-height: 125px;
    width: 100%;
}

svg {
   -webkit-transform: rotate(-90deg);
    transform: rotate(-90deg);
}

.circle_animation {
  stroke-dasharray: 440; /* this value is the pixel circumference of the circle */
  stroke-dashoffset: 440;
  transition: all 1s linear;
}
</style>

