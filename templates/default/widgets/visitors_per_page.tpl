    <div class="mod_bcstats fc_widget_content">
        <table>
            <thead>
                <tr>
                    <th class="fc_gradient2">{translate('Menu title')}</th>
                    <th class="fc_gradient2">{translate('Visits')}</th>
                    <th class="fc_gradient2">{translate('Last visit')}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $visitors item}
                <tr>
                    <td><a href="{$item.link}">{$item.title}</a></td><td>{$item.count}</td><td>{$item.lastseen}</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
