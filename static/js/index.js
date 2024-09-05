/*
 * @Descripttion :
 * @Author       : liuzhifang
 * @Date         : 2022-08-17 14:04:25
 * @LastEditors  : liuzhifang
 * @LastEditTime : 2022-08-18 10:28:06
 */

/* 图表配置
 ****************************************************************************************/
function chartOption({ pvList, uvList, labelList }) {
    return {
        tooltip: {
            trigger: 'axis',
        },
        legend: {
            data: ['PV', 'UV'],
            x: 'right',
            y: 'top',
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true,
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: labelList,
        },
        yAxis: {
            type: 'value',
            axisLine: {
                show: true,
            },
        },
        series: [
            {
                name: 'UV',
                type: 'line',
                stack: 'Total',
                itemStyle: {
                    normal: {
                        color: '#f66000',
                        lineStyle: {
                            color: '#f66000',
                        },
                    },
                },
                data: uvList,
            },
            {
                name: 'PV',
                type: 'line',
                stack: 'Total',
                itemStyle: {
                    normal: {
                        color: '#2d8cf0',
                        lineStyle: {
                            color: '#2d8cf0',
                        },
                    },
                },
                data: pvList,
            },

        ],
    };
}

foxui.tooltip({
    el: '.pv-tip',
    content: '所有用户的访问页面数量总和',
    placement: ['top', 'center'],
});

foxui.tooltip({
    el: '.uv-tip',
    content: '独立访客总人数',
    placement: ['top', 'center'],
});
