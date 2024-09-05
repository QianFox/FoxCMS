/*
 * @Descripttion : 富文本插件
 * @Versions     : 0.1
 * @Author       : foxcms team
 * @Date         : 2022-01-11 19:54:00
 * @LastEditors  : QianFox Team
 * @LastEditTime : 2024-02-29 16:26:54
 */

'use strict';
// 初始化富文本插件
tinymceInit('.richText');

function tinymceInit(selector) {
    tinymce.remove(selector);
    tinymce.init({
        resize: false, // 禁用拖动放大缩小
        selector: selector,
        branding: false, // 不显示右下角权限信息
        convert_urls: false,
        // menubar: false, // 不显示菜单栏
        content_style: 'img {max-width:100%;}',
        forced_root_block: 'p',
        menu: {
            file: { title: '文件', items: 'newdocument | preview' },
            edit: { title: '编辑', items: 'undo redo | cut copy paste | selectall' },
            view: { title: '查看', items: 'visualaid fullscreen' },
            insert: { title: '插入', items: 'link imagesManager videosManager ' },
            format: { title: '格式', items: 'bold italic underline strikethrough superscript subscript indent2em | formats | forecolor backcolor | removeformat' },
            // table: { title: '表格', items: 'inserttable tableprops deletetable | cell row column' },
            tools: { title: '工具', items: 'code' },
        },
        height: '100%',
        language: 'zh_CN',
        plugins: 'preview lists advlist imagesManager videosManager indent2em code fullscreen link pagebreak table paste',
        toolbar:
            'bold italic underline forecolor backcolor | styleselect fontselect fontsizeselect | alignleft aligncenter alignright alignjustify alignnone | table link imagesManager videosManager | blockquote bullist numlist outdent indent indent2em lineheight | removeformat pagebreak code | fullscreen',
        font_formats: '微软雅黑=Microsoft YaHei,Helvetica Neue,PingFang SC,sans-serif;苹果苹方=PingFang SC,Microsoft YaHei,sans-serif;宋体=simsun,serif',
        fontsize_formats: '12px 14px 16px 18px 20px 24px 36px 48px 56px 72px',
        style_formats: [
            { title: '标题1', block: 'h1' },
            { title: '标题2', block: 'h2' },
            { title: '标题3', block: 'h3' },
            { title: '标题4', block: 'h4' },
            { title: '标题5', block: 'h5' },
            { title: '标题6', block: 'h6' },
            { title: '正文', block: 'p' },
        ],
        paste_data_images: true,
        images_upload_handler: function (blobInfo, succFun, failFun) {
            var xhr, formData;
            var file = blobInfo.blob();//转化为易于理解的file对象
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', UPIMG_URL);
            xhr.onload = function() {
                var json;
                if (xhr.status != 200) {
                    failFun('HTTP Error: ' + xhr.status);
                    return;
                }
                json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location != 'string') {
                    //failFun('Invalid JSON: ' + xhr.responseText);
                    failFun(json.msg);
                    return;
                }
                succFun(json.location);
            };
            formData = new FormData();
            formData.append('file', file, file.name);//此处与源文档不一样
            xhr.send(formData);
        }
    });
}

/**
 * @description: 获取富文本内容
 * @param {*}
 * @return {*}
 * @Date: 2022-01-11 21:13:06
 */
function getRichTextContent() {
    return tinyMCE.activeEditor.getContent();
}

/**
 * @description: 获取富文本内容
 * @param $id
 * @return {*}
 * @Date: 2022-01-11 21:13:06
 */
function getRTtContent(id) {
    let count = tinyMCE.editors.length;
    let content = '';
    if (count > 0) {
        for (let i = 0; i < count; i++) {
            let cid = tinyMCE.editors[i].id;
            if (id == cid) {
                content = tinyMCE.editors[i].getContent();
                break;
            }
        }
    }
    return content;
}

/**
 * @description: 设置富文本内容
 * @param {*} content
 * @return {*}
 * @Date: 2022-01-11 21:13:27
 */
function setRichTextContent(content) {
    tinyMCE.activeEditor.setContent(content);
}
