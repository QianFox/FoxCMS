/*
 * @Descripttion :
 * @Author       : liuzhifang
 * @Date         : 2022-06-15 11:19:09
 * @LastEditors  : QianFox Team
 * @LastEditTime : 2024-08-13 09:21:59
 */

// 添加字段
$('.select-field-box .field-item').on('click', function () {
    let $this = $(this),
        $diyContainer = $('#previewDiyForm'),
        $formContainer = $('#diyFormSet'),
        type = $this.attr('data-type'),
        diyId = 'diyid' + new Date().getTime(),
        fieldHtml = '',
        itemHtml = '';

    switch (type) {
        case 'inputtext':
            fieldHtml = _inputPreviewHtml();
            break;
        case 'textarea':
            fieldHtml = _textareaPreviewHtml();
            break;
        case 'radio':
            fieldHtml = _radioPreviewHtml();
            break;
        case 'checkbox':
            fieldHtml = _checkboxPreviewHtml();
            break;
        case 'select':
            fieldHtml = _selectPreviewHtml();
            break;
        case 'switch':
            fieldHtml = _switchPreviewHtml();
            break;
        case 'datepicker':
        case 'timepicker':
        case 'datetimepicker':
            fieldHtml = _datetimepickerPreviewHtml(type);
            break;
        case 'image':
            fieldHtml = _imagePreviewHtml();
            break;
        case 'color':
            fieldHtml = _colorPreviewHtml();
            break;
    }

    switch (type) {
        case 'inputtext':
            itemHtml = _inputDiyHtml();
            break;
        case 'textarea':
            itemHtml = _textareaDiyHtml();
            break;
        case 'radio':
            itemHtml = _radioDiyHtml();
            break;
        case 'checkbox':
            itemHtml = _checkboxDiyHtml();
            break;
        case 'select':
            itemHtml = _selectDiyHtml();
            break;
        case 'switch':
            itemHtml = _switchDiyHtml();
            break;
        case 'datepicker':
        case 'timepicker':
        case 'datetimepicker':
            itemHtml = _datetimepickerDiyHtml(type);
            break;
        case 'image':
            itemHtml = _imageDiyHtml();
            break;
        case 'color':
            itemHtml = _colorDiyHtml();
            break;
    }

    if (fieldHtml && itemHtml) {
        $diyContainer.append(`<div class="item" id="${diyId}" data-type="${type}" style="display:none">` + fieldHtml + '</div>');
        $formContainer.append(
            [
                `<li class="foxui-drag-item" data-diyid="${diyId}" data-type="${type}" style="display:none">`,
                '<div class="foxui-drag-content">',
                '<div class="foxui-row foxui-gutter-6 foxui-align-items-center">',
                itemHtml,
                '</div>',
                '</div>',
                '</li>',
            ].join('')
        );
        $(`#${diyId}`).slideDown();
        $(`.foxui-drag-item[data-diyid="${diyId}"]`).slideDown();
    }
});

// 修改label
$(document).on('input', '#diyFormSet .form-label', function () {
    let $this = $(this),
        value = $this.val(),
        id = $this.closest('.foxui-drag-item').attr('data-diyid');
    $(`#${id} .form-label`).text(value);
});

// 修改 placeholder
$(document).on('input', '#diyFormSet .form-input-placeholder', function () {
    let $this = $(this),
        value = $this.val(),
        id = $this.closest('.foxui-drag-item').attr('data-diyid');
    $(`#${id} .form-input-placeholder`).attr('placeholder', value);
});

// 修改是否必填
$(document).on('click', '#diyFormSet .form-label-require', function () {
    let $this = $(this),
        isChecked = $this.is('.is-checked'),
        id = $this.closest('.foxui-drag-item').attr('data-diyid'),
        $label = $(`#${id} .form-label`);
    isChecked ? $label.addClass('foxui-required') : $label.removeClass('foxui-required');
});


// 删除选项
$(document).on('click', '#diyFormSet .delete-option-btn', function () {
    let $this = $(this),
        $option = $this.closest('.foxui-input-group'),
        index = $option.index(),
        id = $this.closest('.foxui-drag-item').attr('data-diyid'),
        len = $(`#${id}`).find('.form-option').length;

    $(`#${id}`)
        .find('.form-option')
        .eq(index)
        .fadeOut(function () {
            $(this).remove();
        });
    $option.slideUp(function () {
        $(this).remove();
    });
    if (len <= 2) {
        $('#diyFormSet').find('.delete-option-btn').attr('disabled', true).addClass('is-disabled');
    }
});

// 添加选项
$(document).on('click', '#diyFormSet .add-option-btn', function () {
    let $this = $(this),
        $optionContainer = $this.parent().siblings('.option-box'),
        $option = $optionContainer.find('.foxui-input-group'),
        type = $this.attr('data-type'),
        len = $option.length,
        id = $this.closest('.foxui-drag-item').attr('data-diyid'),
        html = '',
        optionHtml = '';

    if (len < 2) {
        $option.find('.delete-option-btn').attr('disabled', false).removeClass('is-disabled');
    }
    if (type === 'radio') {
        html = [
            '<div class="foxui-input-group" style="display:none">',
            '<div class="foxui-input-prepend">',
            '<div class="foxui-prepend-inner is-grey">选项</div>',
            '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
            '</div>',
            '<button class="foxui-text-primary foxui-size-small delete-option-btn margin-left-8">删除</button>',
            '</div>',
        ].join('');
        optionHtml = [
            '<div class="foxui-radio form-option" style="display:none">',
            '<span class="foxui-radio-input">',
            '<i class="foxui-radio-icon"></i>',
            '<input type="radio" value="" />',
            '</span>',
            '<span class="foxui-radio-label option-label">选项</span>',
            '</div>',
        ].join('');
    } else if (type === 'checkbox') {
        html = [
            '<div class="foxui-input-group" style="display:none">',
            '<div class="foxui-input-prepend">',
            '<div class="foxui-prepend-inner is-grey">选项</div>',
            '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
            '</div>',
            '<button class="foxui-text-primary foxui-size-small delete-option-btn">删除</button>',
            '</div>',
        ].join('');
        optionHtml = [
            '<div class="foxui-checkbox form-option" style="display:none">',
            '<span class="foxui-checkbox-input">',
            '<i class="foxui-checkbox-icon"></i>',
            '<input type="checkbox" value="" />',
            '</span>',
            '<span class="foxui-checkbox-label option-label">选项</span>',
            '</div>',
        ].join('');
    } else if (type === 'select') {
        html = [
            '<div class="foxui-input-group" style="display:none">',
            '<div class="foxui-input-prepend">',
            '<div class="foxui-prepend-inner is-grey">选项</div>',
            '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
            '</div>',
            '<button class="foxui-text-primary foxui-size-small delete-option-btn margin-left-8">删除</button>',
            '</div>',
        ].join('');
        optionHtml = ['<li class="foxui-select-item form-option" style="display:none"><span class="option-label">选项</span></li>'].join('');
    }
    $optionContainer.append(html);
    $optionContainer.find('.foxui-input-group:last-child').slideDown();
    $(`#${id}`).find('.form-option-group').append(optionHtml);
    $(`#${id} .form-option-group .form-option:last-child`).fadeIn();
});

// 修改选项
$(document).on('input', '#diyFormSet .option-box .form-input-option', function () {
    let $this = $(this),
        index = $this.closest('.foxui-input-group').index(),
        value = $this.val(),
        id = $this.closest('.foxui-drag-item').attr('data-diyid');

    $(`#${id} .form-option`).eq(index).find('.option-label').text(value);
});

// 修改图片数量
$(document).on('input', '#diyFormSet .image-count', function () {
    let $this = $(this),
        count = $this.val(),
        id = $this.closest('.foxui-drag-item').attr('data-diyid');

    $(`#${id} .foxui-images`).attr('data-count', count);
});

// input 预览 html
function _inputPreviewHtml() {
    return [
        '<div class="foxui-input-group">',
        '<label class="foxui-required form-label">单行文本：</label>',
        '<input class="foxui-size-small form-input-placeholder" placeholder="请输入" style="flex:1" value="" />',
        '</div>',
    ].join('');
}

// input 表单设置 html
function _inputDiyHtml() {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<span>单行文本</span>',
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        '<input class="foxui-size-small form-label title" placeholder="请输入" value="单行文本" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">提示语</div>',
        '<input class="foxui-size-small form-input-placeholder tip" placeholder="请输入" value="请输入" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" value="" checked="checked" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// textarea 预览 html
function _textareaPreviewHtml() {
    return [
        '<div class="foxui-input-group foxui-align-items-start">',
        '<label class="foxui-required form-label">多行文本：</label>',
        '<div class="foxui-textarea" style="flex:1">',
        '<textarea class="form-input-placeholder" autocomplete="off" rows="3" placeholder="请输入"></textarea>',
        '</div>',
        '</div>',
    ].join('');
}

// textarea 表单设置 html
function _textareaDiyHtml() {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<span>多行文本</span>',
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        '<input class="foxui-size-small form-label title" placeholder="请输入" value="多行文本" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">提示语</div>',
        '<input class="foxui-size-small form-input-placeholder tip" placeholder="请输入" value="请输入" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" value="" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// radio 预览 html
function _radioPreviewHtml() {
    return [
        '<div class="foxui-input-group">',
        '<label class="foxui-required form-label">单选：</label>',
        '<div class="foxui-radio-group form-option-group radio-box-multi-line" style="flex:1">',
        '<div class="foxui-radio form-option">',
        '<span class="foxui-radio-input">',
        '<i class="foxui-radio-icon"></i>',
        '<input type="radio" value="" />',
        '</span>',
        '<span class="foxui-radio-label option-label">选项</span>',
        '</div>',
        '<div class="foxui-radio form-option">',
        '<span class="foxui-radio-input">',
        '<i class="foxui-radio-icon"></i>',
        '<input type="radio" value="" />',
        '</span>',
        '<span class="foxui-radio-label option-label">选项</span>',
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

// radio 表单设置 html
function _radioDiyHtml() {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<span>单选</span>',
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        '<input class="foxui-size-small form-label title" placeholder="请输入" value="单选" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '<div class="option-box margin-top-10">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">选项</div>',
        '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
        '</div>',
        '<button class="foxui-text-primary foxui-size-small delete-option-btn margin-left-8">删除</button>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">选项</div>',
        '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
        '</div>',
        '<button class="foxui-text-primary foxui-size-small delete-option-btn margin-left-8">删除</button>',
        '</div>',
        '</div>',
        '<div>',
        '<button class="foxui-text-primary add-option-btn" data-type="radio"><i class="foxui-icon-jiahao-o"></i><span>添加选项</span></button>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" value="" checked="checked" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// checkbox 预览 html
function _checkboxPreviewHtml() {
    return [
        '<div class="foxui-input-group">',
        '<label class="foxui-required form-label">多选项：</label>',
        '<div class="foxui-checkbox-group form-option-group checkbox-box-multi-line" style="flex:1">',
        '<div class="foxui-checkbox form-option">',
        '<span class="foxui-checkbox-input">',
        '<i class="foxui-checkbox-icon"></i>',
        '<input type="checkbox" value="" />',
        '</span>',
        '<span class="foxui-checkbox-label option-label">选项</span>',
        '</div>',
        '<div class="foxui-checkbox form-option">',
        '<span class="foxui-checkbox-input">',
        '<i class="foxui-checkbox-icon"></i>',
        '<input type="checkbox" value="" />',
        '</span>',
        '<span class="foxui-checkbox-label option-label">选项</span>',
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

// checkbox 表单设置 html
function _checkboxDiyHtml() {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<span>多选</span>',
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        '<input class="foxui-size-small form-label title" placeholder="请输入" value="多选" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '<div class="option-box margin-top-10">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">选项</div>',
        '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
        '</div>',
        '<button class="foxui-text-primary foxui-size-small delete-option-btn margin-left-8">删除</button>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">选项</div>',
        '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
        '</div>',
        '<button class="foxui-text-primary foxui-size-small delete-option-btn margin-left-8">删除</button>',
        '</div>',
        '</div>',
        '<div>',
        '<button class="foxui-text-primary add-option-btn" data-type="checkbox"><i class="foxui-icon-jiahao-o"></i><span>添加选项</span></button>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" value="" checked="checked" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// select 预览 html
function _selectPreviewHtml() {
    return [
        '<div class="foxui-input-group">',
        '<label class="foxui-required form-label">下拉框：</label>',
        '<div class="foxui-select" style="flex: 1">',
        '<div class="foxui-select-handle foxui-select-icon">',
        '<input class="foxui-select-input foxui-size-small form-input-placeholder" readonly="readonly" placeholder="请选择" value="" />',
        '</div>',
        '<div class="foxui-select-menu">',
        '<ul class="foxui-select-slide form-option-group">',
        '<li class="foxui-select-item form-option"><span class="option-label">选项</span></li>',
        '<li class="foxui-select-item form-option"><span class="option-label">选项</span></li>',
        '</ul>',
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

// select 表单设置 html
function _selectDiyHtml() {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<span>下拉框</span>',
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        '<input class="foxui-size-small form-label title" placeholder="请输入" value="下拉框" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">提示语</div>',
        '<input class="foxui-size-small form-input-placeholder tip" placeholder="请输入" value="请选择" />',
        '</div>',
        '</div>',
        '<div class="option-box margin-top-10">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">选项</div>',
        '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
        '</div>',
        '<button class="foxui-text-primary foxui-size-small delete-option-btn margin-left-8">删除</button>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">选项</div>',
        '<input class="foxui-size-small form-input-option" placeholder="请输入" value="选项" />',
        '</div>',
        '<button class="foxui-text-primary foxui-size-small delete-option-btn margin-left-8">删除</button>',
        '</div>',
        '</div>',
        '<div>',
        '<button class="foxui-text-primary add-option-btn" data-type="select"><i class="foxui-icon-jiahao-o"></i><span>添加选项</span></button>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" checked="checked" value="" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// switch 预览 html
function _switchPreviewHtml() {
    return [
        '<div class="foxui-input-group">',
        '<label class="foxui-required form-label">Switch开关：</label>',
        '<div class="switch" style="flex:1">',
        '<div class="foxui-switch is-checked">',
        '<input type="checkbox" checked="checked" value="" class="foxui-switch-input" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

// switch 表单设置 html
function _switchDiyHtml() {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<span>Switch开关</span>',
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        '<input class="foxui-size-small form-label title" placeholder="请输入" value="Switch开关" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" checked="checked" value="" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// datepicker、timepicker、datetimepicker 预览 html
function _datetimepickerPreviewHtml(type) {
    return [
        '<div class="foxui-input-group">',
        `<label class="foxui-required form-label">${type === 'datepicker' ? '日期' : type === 'timepicker' ? '时间' : '日期时间'}：</label>`,
        `<div class="foxui-picker ${type === 'datepicker' ? 'foxui-date-picker' : type === 'timepicker' ? 'foxui-time-picker has-seconds' : 'foxui-datetime-picker has-seconds'}" style="flex:1">`,
        '<div class="foxui-picker-handle foxui-input-prefix">',
        `<i class="${type === 'datepicker' ? 'foxui-icon-rili-o' : type === 'timepicker' ? 'foxui-icon-gongzuo-o' : 'foxui-icon-riqi-o'} foxui-prefix-icon"></i>`,
        '<input class="foxui-size-small form-input-placeholder" readonly="readonly" placeholder="请选择" value="" />',
        '<i class="foxui-icon-close-circle foxui-suffix-icon foxui-suffix-clear"></i>',
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

// datepicker、timepicker、datetimepicker 表单设置 html
function _datetimepickerDiyHtml(type) {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        `<span>${type === 'datepicker' ? '日期' : type === 'timepicker' ? '时间' : type === 'datetimepicker' ? '日期时间' : ''}</span>`,
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        `<input class="foxui-size-small form-label title" placeholder="请输入" value="${
            type === 'datepicker' ? '日期' : type === 'timepicker' ? '时间' : type === 'datetimepicker' ? '日期时间' : ''
        }" />`,
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">提示语</div>',
        '<input class="foxui-size-small form-input-placeholder tip" placeholder="请输入" value="请选择" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" checked="checked" value="" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// image 预览 html
function _imagePreviewHtml() {
    return [
        '<div class="foxui-input-group foxui-align-items-start">',
        '<label class="foxui-required form-label">图片文件：</label>',
        '<div class="foxui-images">',
        '<div class="foxui-images-card">',
        '<ul class="foxui-images-list">',
        '<div class="foxui-images-handle">',
        '<div class="foxui-images-handle-inner">',
        '<i class="foxui-icon-jiahao-o"></i>',
        '<span class="text">添加图片</span>',
        '</div>',
        '</div>',
        '</ul>',
        '</div>',
        ' </div>',
        ' </div>'
    ].join('');
}

// image 表单设置 html
function _imageDiyHtml() {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<span>图片文件</span>',
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        '<input class="foxui-size-small form-label title" placeholder="请输入" value="图片文件" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">上传图片数量</div>',
        '<input class="foxui-size-small image-count" placeholder="请输入说明文字" value="1" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" checked="checked" value="" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// color 预览 html
function _colorPreviewHtml() {
    return [
        '<div class="foxui-input-group">',
        '<label class="foxui-required form-label">颜色选择：</label>',
        '<div class="foxui-color">',
        '<div class="foxui-color-handle is-alpha">',
        '<span class="foxui-color-show"></span>',
        '<i class="foxui-icon-close" style="display: none"></i>',
        '<span class="foxui-color-background"></span>',
        '<input class="foxui-color-input" value="" />',
        '</div>',
        '</div>',
        '</div>',
    ].join('');
}

// color 表单设置 html
function _colorDiyHtml() {
    return [
        '<div class="foxui-col-xs-2 foxui-col-sm-2 display-flex foxui-justify-content-center">',
        '<span class="foxui-drag-handle foxui-icon-liebiao-o"></span>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<span>颜色选择</span>',
        '</div>',
        '<div class="foxui-col-xs-5 foxui-col-sm-5">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">标题</div>',
        '<input class="foxui-size-small form-label title" placeholder="请输入" value="颜色选择" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-9 foxui-col-sm-9">',
        '<div class="foxui-input-group">',
        '<div class="foxui-input-prepend">',
        '<div class="foxui-prepend-inner is-grey">说明</div>',
        '<input class="foxui-size-small explain" placeholder="请输入说明文字" value="" />',
        '</div>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-4 foxui-col-sm-4">',
        '<div class="display-flex foxui-justify-content-center">',
        '<div class="foxui-switch is-checked form-label-require">',
        '<input type="checkbox" value="" checked="checked" class="foxui-switch-input required" />',
        '<span class="foxui-switch-core"></span>',
        '</div>',
        '<label class="margin-left-8">必填</label>',
        '</div>',
        '</div>',
        '<div class="foxui-col-xs-2 foxui-col-sm-2">',
        '<button class="foxui-text-primary foxui-size-small delete-item-btn">删除</button>',
        '</div>',
    ].join('');
}

// 滚动定位
let $selectFieldBox = $('.select-field-box'),
    sHeight = $selectFieldBox.height();
$('.diy_form_edit_content .foxui-col-sm-16 .section').on('scroll', function () {
    let scollTop = $(this).scrollTop();
    if (scollTop >= 260) {
        $selectFieldBox.addClass('is-fixed');
        $selectFieldBox.closest('.section-main').css('height', `${sHeight}px`);
    } else {
        $selectFieldBox.removeClass('is-fixed');
        $selectFieldBox.closest('.section-main').css('height', `auto`);
    }
});

// 拖拽排序
$(document).on('dragend', '.foxui-drag.foxui-drag-container', function () {
    let diyidList = [],
        htmlList = [];
    $('#diyFormSet .foxui-drag-item').each(function () {
        diyidList.push($(this).attr('data-diyid'));
    });
    $('#previewDiyForm .item').each(function () {
        htmlList.push(this);
    });
    // 清空
    $('#previewDiyForm').empty();
    // 追加节点
    diyidList.forEach(id => {
        htmlList.forEach(html => {
            if ($(html).attr('id') === id) {
                $('#previewDiyForm').append(html);
                return false;
            }
        });
    });
});
