$(document).ready(function()
{
    if (typeof(window.cloudZoomOpts) == "undefined")
    {
        window.cloudZoomOpts = {};
    }
    $('.cloud-zoom, .cloud-zoom-gallery').CloudZoom(window.cloudZoomOpts);
});