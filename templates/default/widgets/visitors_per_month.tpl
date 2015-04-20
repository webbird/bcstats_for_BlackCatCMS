    <div class="fc_widget_content">
        {translate('Choose')}:
        <select id="month">
            {foreach $months num month}<option value="{$num}">{translate($month)}</option>{/foreach}
        </select>
        <select id="year" name="year">
            {foreach $years year}<option value="{$year}">{$year}</option>{/foreach}
        </select>
        <button id="widget_visitors_per_month">{translate('Update')}</button><br /><br />

        {if $chart}
        <div id="widget_visitors_per_month_chart">
        {$chart}
        </div>
        {else}
        <table class="cal_month">
            <thead>
                <tr><th colspan="7" class="monthname">{translate($monthname)} {$year}</th></tr>
                <tr>
                    <th>{translate('Mon')}</th>
                    <th>{translate('Tue')}</th>
                    <th>{translate('Wed')}</th>
                    <th>{translate('Thu')}</th>
                    <th>{translate('Fri')}</th>
                    <th>{translate('Sat')}</th>
                    <th>{translate('Sun')}</th>
                </tr>
            </thead>
            <tbody>
                 {$calsheet}
            </tbody>
        </table>
        {/if}
    </div>
    

<script charset="windows-1250" type="text/javascript">
    var monthnames = {
        {foreach $months num month}
        {$num}: "{$month}"{if not $.foreach.default.last},{/if}
        {/foreach}
    };
    $('button#widget_visitors_per_month').unbind('click').bind('click', function(event) {
        event.preventDefault();
        $.ajax({
            url: "{$CAT_URL}/modules/BCStats/widgets/visitors_per_month.php",
            data: {
                year     : $('#year').val(),
                month    : $('#month').val(),
                _cat_ajax: 1
            },
            dataType: 'json',
            success: function(data) {
                if(data.type == 'table')
                {
                    $('tbody').html(data.content);
                }
                else
                {
                    $('div#widget_visitors_per_month_chart').html(data.content);
                }
                $('.monthname').text(
                    cattranslate( monthnames[ $('#month').val() ], '', '', 'BCStats' ) + ' ' + $('#year').val()
                );
            },
        });
    });
</script>
