/*
 * @Descripttion : 百度地图
 * @Author       : liuzhifang
 * @Date         : 2022-06-15 08:12:07
 * @LastEditors  : liuzhifang
 * @LastEditTime : 2022-06-15 08:50:17
 */

'use strict';

var bmap = (function () {
    let map = null;
    let mapData = {
        lng: '',
        lat: '',
    };

    /**
     * @description: 初始化地图
     * @param {*}
     * @return {*}
     * @Date: 2021-09-15 14:00:07
     */
    function init(ak) {
        window.onload = _appendBmapJs(ak);
    }

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
     * @description: 向 html 中添加百度地图 sdk (ak: n255HlgxO7hR9TClb3skfwcpl7oUGMri)
     * @param {*}
     * @return {*}
     * @Date: 2021-09-15 14:00:41
     */
    function _appendBmapJs(ak) {
        const src = `https://api.map.baidu.com/api?v=1.0&type=webgl&ak=${ak}&callback=initialize`;
        let head = document.head || document.getElementsByTagName('head')[0];
        let script = document.createElement('script');
        script.type = 'text/javascript';
        script.setAttribute('src', src);
        head.appendChild(script);
    }

    /**
     * @description: 添加地图控件
     * @param {*}
     * @return {*}
     * @Date: 2021-09-15 13:40:46
     */
    function _setOption() {
        // 添加比例尺控件
        let scaleCtrl = new BMapGL.ScaleControl();
        map.addControl(scaleCtrl);
        // 添加缩放控件
        let zoomCtrl = new BMapGL.ZoomControl();
        map.addControl(zoomCtrl);
    }

    /**
     * @description: 开始绘制地图
     * @param {*} lng
     * @param {*} lat
     * @param {*} zoom
     * @return {*}
     * @Date: 2021-09-15 13:40:06
     */
    function drawMap({ lng, lat, zoom }) {
        // 重置数据
        _restMapData();
        // 创建Map实例
        map = new BMapGL.Map('mapForPosition');
        // 初始化地图,设置中心点坐标和地图级别
        map.centerAndZoom(new BMapGL.Point(lng, lat), zoom);
        //开启鼠标滚轮缩放
        map.enableScrollWheelZoom(true);
        // 设置地图旋转角度
        map.setHeading(64.5);
        // 设置地图的倾斜角度
        map.setTilt(73);
        // 设置地图配置
        _setOption();
        // 添加点
        drawMarker(lng, lat);
    }

    /**
     * @description: 添加标注点（允许拖动并获取经纬度）
     * @param {*} lng
     * @param {*} lat
     * @return {*}
     * @Date: 2021-09-15 13:39:14
     */
    function drawMarker(lng, lat) {
        let point = new BMapGL.Point(lng, lat);
        // 创建标注, 允许拖拽
        let marker = new BMapGL.Marker(point, {
            enableDragging: true,
        });
        // 将标注添加到地图中
        map.addOverlay(marker);
        // 绑定拖动结束事件，获取经纬度
        marker.addEventListener('dragend', function () {
            let curPoint = marker.getPosition();
            // 保存经纬度
            mapData.lng = curPoint.lng;
            mapData.lat = curPoint.lat;
        });
    }

    /**
     * @description: 通过地址获取经纬度
     * @param {*} seachWord
     * @return {*}
     * @Date: 2021-09-15 13:51:50
     */
    function searchPosition(seachWord) {
        let localSearch = new BMapGL.LocalSearch(map);
        // 允许自动调节窗体大小
        localSearch.enableAutoViewport();
        localSearch.setSearchCompleteCallback(function (searchResult) {
            // 获取搜索结果第一个
            let poi = searchResult.getPoi(0);
            if (poi) {
                // 中心点坐标和地图级别
                map.centerAndZoom(poi.point, 19);
                // 清空原来的标注
                map.clearOverlays();
                // 添加点(搜索结果第一个的经纬度)
                drawMarker(poi.point.lng, poi.point.lat);
                // 保存经纬度
                mapData.lng = poi.point.lng;
                mapData.lat = poi.point.lat;
                // 返回数据
                getData();
            } else {
                foxui.message({
                    type: 'danger',
                    text: '没有找到相应的位置，请重新填写',
                });
            }
        });
        localSearch.search(seachWord);
    }

    /**
     * @description: 返回地图数据
     * @param {*}
     * @return {*}
     * @Date: 2021-09-15 13:50:16
     */
    function getData() {
        return mapData;
    }

    // 初始化
    // _init('n255HlgxO7hR9TClb3skfwcpl7oUGMri');

    return { init, drawMap, drawMarker, searchPosition, getData };
})();
