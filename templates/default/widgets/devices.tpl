    <div class="fc_widget_content accordion">
            {foreach $devices type items}
            <h3><span class="mod_bcstats_left">{$type}</span><span class="mod_bcstats_right">{$items.sum}</span></h3>
                {foreach $items item}{if is_array($item)}
                <div>
                <span class="mod_bcstats_left">{$item.platform}</span><span class="mod_bcstats_right">{$item.count}</span>
                </div>
                {/if}{/foreach}
            {/foreach}
    </div>
    {$chart}