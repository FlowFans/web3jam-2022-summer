$(document).ready(function(){

    $('.tooltip').tooltipster({
        // plugins: ['follower'],
        content: '...',
        contentAsHTML: true,
        interactive: true,
        animation: 'fade',
        delay: 600,
        // trigger: 'click',
        minWidth: 320,
        arrow: false,
        functionBefore: function(origin, continueTooltip) {
            //本匿名函数是异步的，使得AJAX正在获取数据的同时，工具提示能弹出来并显示“正在加载中…”（注：根据文档，functionBefore函数返回后，工具提示才能弹出）
    
            continueTooltip();
    
            //检测内容是否已缓存（若已经缓存，则无需重新获取）
            var datainfo = $(this).attr('datainfo');
            if (origin.data('ajax') !== 'cached') {
                $.ajax({
                    type: 'get',
                    url: '/renas/tooltip/index.php?datainfo='+datainfo,
                    success: function(data) {
                        //用通过AJAX获得的内容来替换原有内容，并把状态标记为“cached(已缓存)”
                        origin.tooltipster('content', data).data('ajax', 'cached');
                    },
                    // dataType: 'html'
                });
            }
        }
    });
});

