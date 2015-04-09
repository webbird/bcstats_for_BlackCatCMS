    <div class="fc_widget_title">{translate('Browsers')}</div>
    <div class="fc_widget_content accordion">
        {foreach $browsers name data}
        <h3><span class="mod_bcstats_left"><span class="browser_sprite {$name}"></span> {$name} ({$data.maker})</span><span class="mod_bcstats_right">{$data.sum}</sum></h3>
        <div>
            {foreach $data.data item}
            <span class="mod_bcstats_left">{$item.version}</span><span class="mod_bcstats_right">{$item.count}</span><br />
            {/foreach}
        </div>
        {/foreach}
    </div>
    <canvas class="chart" id="browserChart" width="300" height="300"></canvas>
    <div id="browserLegend"></div>

<script charset=windows-1250 type="text/javascript">
    resetColors();
    var browserChartData = [];
    {foreach $browsers name data}{foreach $data.data item}
    blend1 = 0.1 * counter;
    color1 = shadeBlend(blend1,color1);
    //color2 = shadeBlend(blend1,color2);
    browserChartData.push({
        value: {$item.count},
        color: color1,
        highlight: color2,
        label: "{$name} {$item.version}"
    });
    counter = counter + 1;
    {/foreach}{/foreach}
    jQuery('#browserChart').data('chart',browserChartData);
</script>