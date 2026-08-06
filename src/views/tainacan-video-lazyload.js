const activateLazyVideo = (placeholder) => {
    if (!placeholder || !placeholder.parentNode)
        return;

    const videoSrc = placeholder.getAttribute('data-video-src');
    if (!videoSrc)
        return;

    const video = document.createElement('video');
    video.className = 'tainacan-video-lazyload__video';
    video.setAttribute('controls', 'controls');
    video.setAttribute('preload', 'none');
    video.setAttribute('playsinline', 'playsinline');
    video.setAttribute('autoplay', 'autoplay');
    video.src = videoSrc;

    ['click', 'pointerdown', 'touchstart'].forEach((eventType) => {
        video.addEventListener(eventType, (event) => event.stopPropagation());
    });

    ['width', 'height'].forEach((dimension) => {
        const value = placeholder.getAttribute(`data-video-${dimension}`);
        if (value && /^[1-9][0-9]*$/.test(value))
            video.setAttribute(dimension, value);
    });

    document.querySelectorAll('.tainacan-video-lazyload__video').forEach((activeVideo) => {
        if (activeVideo !== video && typeof activeVideo.pause === 'function')
            activeVideo.pause();
    });

    placeholder.parentNode.replaceChild(video, placeholder);

    try {
        const playback = video.play();
        if (playback && typeof playback.catch === 'function')
            playback.catch(() => {});
    } catch {
        // The controls remain available if the browser rejects immediate playback.
    }
};

const handleLazyVideoActivation = (event) => {
    const target = event.target;
    if (!target || typeof target.closest !== 'function')
        return;

    const placeholder = target.closest('.tainacan-video-lazyload');
    if (!placeholder)
        return;

    if (event.type === 'keydown' && event.key !== 'Enter' && event.key !== ' ') {
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    activateLazyVideo(placeholder);
};

const initializeLazyVideos = () => {
    document.addEventListener('click', handleLazyVideoActivation, true);
    document.addEventListener('keydown', handleLazyVideoActivation, true);
};

if (typeof document !== 'undefined') {
    if (/comp|inter|loaded/.test(document.readyState))
        initializeLazyVideos();
    else
        document.addEventListener('DOMContentLoaded', initializeLazyVideos, false);
}

export { activateLazyVideo, handleLazyVideoActivation, initializeLazyVideos };
