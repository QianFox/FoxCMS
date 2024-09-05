/*
 * @Descripttion : 百度地图
 * @Versions     : 0.1
 * @Author       : foxcms team
 * @Date         : 2022-05-09 21:29:15
 * @LastEditors  : Please set LastEditors
 * @LastEditTime : 2023-01-30 15:02:48
 */

'use strict';

var bmap = (function () {
    let rotAngle = -30,
        tiltAngle = 65,
        minZoom = 18,
        maxZoom = 20,
        zoom = 18,
        skyColors = ['rgba(23, 23, 23, 0)', 'rgba(23, 23, 23, 0.2)'],
        markerWidth = 160,
        markerHeight = 160;
    let map = null,
        styleId = null;
    let mapData = {
        lng: '',
        lat: '',
    };

    /**
     * @description: 重置数据
     * @param {*}
     * @return {*}
     * @Date: 2021-09-15 15:27:17
     */
    function _restMapData() {
        // 清空经纬度
        mapData.lng = '';
        mapData.lat = '';
    }

    /**
     * @description: 添加地图控件、配置
     * @param {*}
     * @return {*}
     * @Date: 2021-09-15 13:40:46
     */
    function _setOption() {
        // 是否开启鼠标滚轮缩放
        map.enableScrollWheelZoom();
        // 设置地图旋转角度
        map.setHeading(rotAngle);
        // 设置地图的倾斜角度
        map.setTilt(tiltAngle);
        // 设置地图样式 id
        map.setMapStyleV2({
            styleId,
        });
        // 设置最小级别
        map.setMinZoom(minZoom);
        // 设置最大级别
        map.setMaxZoom(maxZoom);
        // 设置天空的颜色
        map.setDisplayOptions({
            skyColors,
        });
    }

    /**
     * @description: 进入地图后的动画效果
     * @param {*} lng
     * @param {*} lat
     * @return {*}
     * @Date: 2022-05-13 11:03:30
     */
    function startAnimation({ lng, lat }) {
        // 定义关键帧 及 配置
        let keyFrames = [
                {
                    center: new BMapGL.Point(lng, lat),
                    zoom,
                    tilt: 0,
                    heading: 120,
                    percentage: 0,
                },
                {
                    center: new BMapGL.Point(lng, lat),
                    zoom: zoom + 2,
                    tilt: tiltAngle,
                    heading: rotAngle,
                    percentage: 0.6,
                },
                {
                    center: new BMapGL.Point(lng, lat),
                    zoom,
                    tilt: tiltAngle,
                    heading: rotAngle,
                    percentage: 1,
                },
            ],
            opts = {
                duration: 4000,
                delay: 500,
                interation: 1,
            };
        // 声明动画对象
        let animation = new BMapGL.ViewAnimation(keyFrames, opts);
        // 开始播放动画
        map.startViewAnimation(animation);
    }

    /**
     * @description: 开始绘制地图
     * @param {*} id
     * @param {*} lng
     * @param {*} lat
     * @param {*} zoom
     * @return {*}
     * @Date: 2021-09-15 13:40:06
     */
    function drawMap(id, { lng, lat, zoom: initZoom, styleId: initStyleId, title, content }) {
        // 保存数据
        zoom = parseInt(initZoom) ? parseInt(initZoom) : 18;
        styleId = initStyleId || '';
        // 重置数据
        _restMapData();
        // 创建Map实例
        map = new BMapGL.Map(id);
        // 初始化地图,设置中心点坐标和地图级别
        map.centerAndZoom(new BMapGL.Point(lng, lat), zoom);
        // 设置地图配置
        _setOption();
        // 添加富文本内容
        drawRichMarker({ lng, lat, title, content });
        // 添加进入动画
        startAnimation({ lng, lat });
    }

    /**
     * @description: 添加富文本内容
     * @param {*} lng
     * @param {*} lat
     * @param {*} html
     * @param {*} offX
     * @param {*} offY
     * @return {*}
     * @Date: 2022-05-11 09:47:18
     */
    function drawRichMarker({ lng, lat, html, offX, offY, title, content }) {
        html = html || '<div class="map-scale-circle"></div>';
        offX = offX || -markerWidth / 2;
        offY = offY || -markerHeight / 2;
        let point = new BMapGL.Point(lng, lat);
        let richMarker = new BMapGLLib.RichMarker(html, point, {
            anchor: new BMapGL.Size(offX, offY),
            enableDragging: false,
        });
        richMarker.addEventListener('click', function () {
            window.open(`http://api.map.baidu.com/marker?location=${lat},${lng}&title=${title || ''}&content=${content || ''}&output=html`);
        });
        map.addOverlay(richMarker);
    }

    return { drawMap };
})();
