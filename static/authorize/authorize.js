
// 下载证书
$(document).on('click', '#downCertificateBtn', function () {
    html2canvas(document.querySelector('#authorizeCertificate')).then(canvas => {
        // document.body.appendChild(canvas);
        saveFile(canvas.toDataURL('image/png'), 'FOXCMS授权书.png');
    });
});

// 证书图片
// function convertCanvasToImage(canvas) {
//     var image = new Image();
//     image.src = canvas.toDataURL('image/png');
//     return image;
// }

// 保存证书
function saveFile(data, filename) {
    var save_link = document.createElementNS('http://www.w3.org/1999/xhtml', 'a');
    save_link.href = data;
    save_link.download = filename;
    var event = document.createEvent('MouseEvents');
    event.initMouseEvent('click', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
    save_link.dispatchEvent(event);
}
