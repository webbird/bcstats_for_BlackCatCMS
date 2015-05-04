<div class="fc_widget_content">
    <form action="{$CAT_ADMIN_URL}/admintools/tool.php" method="POST">
        <input type="hidden" name="tool" value="BCStats" />
        <input type="hidden" name="action" value="settings" />
        <input type="hidden" name="_cat_ajax" value="1" />
        <label for="reload_time">{translate('Reload time')}</label>
            <input type="text" name="reload_time" value="{$settings.reload_time}" /><br />
            <span>{translate('If a visitor comes back within this time (in seconds), he will not be counted again.')}</span><br />
        {if $can_change_browscapini}
        <label for="browscapini">{translate('Browscap.ini to use')}</label>
            <select id="browscapini" name="browscapini">
                <option value="full"{if $settings.browscapini == 'full'} selected="selected"{/if}>{translate('full (get all info)')}</option>
                <option value="basic"{if $settings.browscapini == 'basic'} selected="selected"{/if}>{translate('basic (no bot detection, very basic information)')}</option>
                <option value="lite"{if $settings.browscapini == 'lite'} selected="selected"{/if}>{translate('lite (very low information only)')}</option>
            </select><br />
        {/if}
        {if $dashboard_support}
        <label for="preferred_layout">{translate('Dashboard layout')}</label>
            <select id="preferred_layout" name="preferred_layout">
                <option value="33-33-33"{if $settings.preferred_layout == '33-33-33'} selected="selected"{/if}>{translate('3 columns')}</option>
                <option value="50-50"{if $settings.preferred_layout == '50-50'} selected="selected"{/if}>{translate('2 columns')}</option>
            </select><br />
        <span>{translate('Please note: The dashboard will be reset when changing this value! All your settings will be lost!')}</span><br />
        {/if}
        <hr />
        <span>{translate('Charts will make the dashboard load slower, but look nicer')}</span><br />
        <label for="show_charts">{translate('Show charts')}</label>
            <input type="checkbox" id="show_charts" name="show_charts" value="Y"{if $settings.show_charts == 'Y'} checked="checked"{/if} /><br />
        <label for="charttype" id="charttype">{translate('Chart type')}</label>
            <select id="charttype" name="charttype">
                <option value="pie"{if $settings.charttype == 'pie'} selected="selected"{/if}>{translate('Pie')}</option>
                <option value="doughnut"{if $settings.charttype == 'doughnut'} selected="selected"{/if}>{translate('Doughnut')}</option>
                <option value="polar"{if $settings.charttype == 'polar'} selected="selected"{/if}>{translate('Polar')}</option>
            </select><br />
        <label for="chroma_scale">{translate('Chroma scale')}</label>
            <select name="chroma_scale" id="chroma_scale">
                {foreach $supported_scales scale}
                <option value="{$scale}"{if $scale == $settings.chroma_scale} selected="selected"{/if}>{$scale}</option>
                {/foreach}
            </select><br />
        <hr />
        <label for="map_view">{translate('Default view for visitors map')}</label>
            <select id="map_view" name="map_view">
                <option value="europe"{if $settings.map_view == 'europe'} selected="selected"{/if}>{translate('Europe')}</option>
                <option value="africa"{if $settings.map_view == 'africa'} selected="selected"{/if}>{translate('Africa')}</option>
                <option value="america"{if $settings.map_view == 'america'} selected="selected"{/if}>{translate('America')}</option>
                <option value="asia"{if $settings.map_view == 'asia'} selected="selected"{/if}>{translate('Asia')}</option>
                <option value="australia"{if $settings.map_view == 'australia'} selected="selected"{/if}>{translate('Australia')}</option>
                <option value="world"{if $settings.map_view == 'world'} selected="selected"{/if}>{translate('World')}</option>
            </select><br /><br />
        <input type="submit" value="{translate('Save')}" />
    </form>
</div>
{if $changes > 0}
<script charset="utf-8" type="text/javascript">
    window.location = window.location.href;
</script>
{/if}