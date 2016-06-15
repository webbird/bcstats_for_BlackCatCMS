    {if $browsers}
    <div class="fc_widget_content bcstats_accordion">
        {foreach $browsers group data}
        <h3>
            <span class="mod_bcstats_left">
                <span class="browser_sprite {$group}"></span>
                {$data.title}
            </span>
            <span class="mod_bcstats_right">{$data.sum}</sum>
        </h3>
        <div>
            {foreach $data item}{if is_array($item)}
            <span class="mod_bcstats_left">{$item.version}</span><span class="mod_bcstats_right">{$item.count}</span><br />
            {/if}{/foreach}
        </div>
        {/foreach}
    </div>
    {$chart}
    {else}
    {translate('No data')}
    {/if}
