<div>
    <form id="change_map">
        <label for="map">{translate('View')}:</label>
        <select name="map" id="map">
            <option value="europe"{if $settings.map_view == 'europe'} selected="selected"{/if}>{translate('Europe')}</option>
            <option value="africa"{if $settings.map_view == 'africa'} selected="selected"{/if}>{translate('Africa')}</option>
            <option value="america"{if $settings.map_view == 'america'} selected="selected"{/if}>{translate('America')}</option>
            <option value="asia"{if $settings.map_view == 'asia'} selected="selected"{/if}>{translate('Asia')}</option>
            <option value="australia"{if $settings.map_view == 'australia'} selected="selected"{/if}>{translate('Australia')}</option>
            <option value="world"{if $settings.map_view == 'world'} selected="selected"{/if}>{translate('World')}</option>
        </select>
    </form><br />
    <div id="mod_bcstats_worldmap">
        <div id="mod_bcstats_overlay">{translate('Loading...')}</div>
    </div>
</div>

<script charset="windows-1250" type="text/javascript">
    var div    = $('#mod_bcstats_worldmap');
    var width  = div.width();

    var map   = kartograph.map('#mod_bcstats_worldmap', width, width);
    var scale = chroma.scale('{$settings.chroma_scale}');
    var countries;

    $.fn.qtip.defaults.style.classes = 'qtip-bootstrap';
    $.fn.qtip.defaults.style.def = false;

    $.getJSON(CAT_URL + '/modules/BCStats/ajax/ajax_get_countries.php', function(countries) {
        var view  = $('select#map option:selected').val();
        $.get(CAT_URL + '/modules/BCStats/js/' + view + '.svg', function(svg) {
            load_map(map,svg,countries);
        }).complete(function() {
            $('#mod_bcstats_overlay').hide();
        });
        $('select#map').on('change',function() {
            $('#mod_bcstats_overlay').show();
            $.get(CAT_URL + '/modules/BCStats/js/' + $(this).val() + '.svg', function(svg) {
                $('#mod_bcstats_overlay').show('fast',function() {
                    map.clear();
                    load_map(map,svg,countries);
                    $('#mod_bcstats_overlay').hide();
                });
            });
        });
    });

    function load_map(map,svg,countries) {
        var scale = chroma.scale('{$settings.chroma_scale}').domain(countries, 7, 'quantiles', 'count');
        map.setMap(svg);
        map.addLayer('countries', {
            styles: {
                'stroke-width': 0.7,
                stroke: function(d) { // Linien
                    return '#000';
                },
                fill: function(d) {   // Fuellfarbe
                    if(d.iso.length) {
                        var iso = d.iso;
                        if(typeof countries[iso] != 'undefined') {
                            return scale(countries[iso].count);
                        }
                        iso = iso.toLowerCase();
                        if(typeof countries[iso] != 'undefined') {
                            return scale(countries[iso].count);
                        }
                    }
                    return chroma('#f0f0f0');
                }
            },
            tooltips: function(d) {
                if(d.iso.length) {
                    var iso = d.iso;
                    if(typeof countries[iso] != 'undefined') {
                        return [countries[iso].country, countries[iso].count + ' (' + countries[iso].lastseen + ')'];
                    }
                    iso = iso.toLowerCase();
                    if(typeof countries[iso] != 'undefined') {
                        return [countries[iso].country, countries[iso].count + ' (' + countries[iso].lastseen + ')'];
                    }
                    return [d.name, "0"];
                }
            }
        });
    }
</script>


