<?php
/**
 * This file is part of the <Translation> project.
 *
 * @subpackage   Translation_Util
 * @package    Extension_jquery
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @since 2012-03-01
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sfynx\CoreBundle\Layers\Domain\Service\Util\PiJquery;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Sfynx\CoreBundle\Layers\Domain\Service\Request\Generalisation\RequestInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Sfynx\ToolBundle\Twig\Extension\PiJqueryExtension;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\ExtensionException;

/**
 * GridTable Jquery plugin
 *
 * @subpackage   Translation_Util
 * @package    Extension_jquery *
 * @author Etienne de Longeaux <etienne.delongeaux@gmail.com>
 */
class PiGridTableManager extends PiJqueryExtension
{
    /** @var CsrfTokenManagerInterface $securityManager */
    protected $securityManager;
   /** @var RequestInterface */
    protected $request;
    /** @var string */
    protected $projectWebDir;

    /**
     * @var array
     * @static
     */
    static $types = ['simple', 'bootstrap'];

   /**
    * PiGridTableManager constructor.
    * @param CsrfTokenManagerInterface $securityManager
    * @param RequestInterface $request
    * @param ContainerInterface $container
    * @param TranslatorInterface $translator
    */
    public function __construct(
            CsrfTokenManagerInterface $securityManager,
            RequestInterface $request,
            ContainerInterface $container,
            TranslatorInterface $translator
    ) {
        $this->securityManager = $securityManager;
        $this->request = $request;

        parent::__construct($container, $translator);
    }

    /**
     * @return string
     */
    public function getProjectWebDir()
    {
        $this->projectWebDir = ($this->projectWebDir && $this->request->server) ?? $this->request->server->get('DOCUMENT_ROOT') . '/';

        return $this->projectWebDir;
    }

    /**
     * Sets init.
     *
     * <code>
     *
     *     {% set options_gridtabale = {'grid-name': 'grid', 'grid-type':'simple',
     *      'grid-server-side': true,
     *      'grid-state-save': false,
     *      'grid-pagination-type': 'full_numbers',
     *      'grid-paginate': true'
     *      'grid-paginate-top': false,
     *      'grid-LengthMenu': 10,
     *      'grid-row-select': 'multi',
     *      'grid-filters-tfoot-up': false,
     *      'grid-filters-active': true,
     *      'grid-filters': {
     *          '4':'Prénom',
     *          '5':'Nom',
     *          '6':'Email',
     *      },
     *      'grid-filter-date': {
     *          '0': {'column' : 7, 'title-start': 'date min crea. ', 'title-end': 'date max crea. ', 'right':'449', 'width':'197', 'format' : 'yy-mm-dd', 'idMin':'minc', 'idMax':'maxc'},
     *          '1': {'column' : 8, 'title-start': 'date min mod. ', 'title-end': 'date max mod. ', 'right':'291', 'width':'179', 'format' : 'yy-mm-dd', 'idMin':'minu', 'idMax':'maxu'},
     *      },
     *      'grid-filters-select': ['0','4','5', '6'],
     *      'grid-filters': {
     *          '1':'Identifiant',
     *       },
     *      'grid-sorting': {
     *          '1':'desc',
     *       },
     *      'grid-columns': {
     *          '0': { "bSortable": true },
     *          '1': { "bSortable": true },
     *       },
     *       'grid-visible': {
     *          '0': false,
     *       },
     *      'grid-actions': {
     *           'select_all': {'sButtonText':'pi.grid.action.select_all'},
     *           'select_none': {'sButtonText':'pi.grid.action.select_none'},
     *           'rows_enabled': {'sButtonText':'pi.grid.action.row_enabled', 'route':'sfynx_layout_enabledentity_ajax'},
     *           'rows_disable': {'sButtonText':'pi.grid.action.row_disable', 'route':'sfynx_layout_disablentity_ajax'},
     *           'rows_delete': {'sButtonText':'pi.grid.action.row_delete', 'route':'sfynx_layout_deletentity_ajax'},
     *           'rows_archive': {'sButtonText':'pi.grid.action.row_archive', 'route':'sfynx_layout_archiventity_ajax', 'reload':true},
     *           'copy': {'sButtonText':'pi.grid.action.copy'},
     *           'print': {'sButtonText':'pi.grid.action.print'},
     *           'export_pdf': {'sButtonText':'pi.grid.action.export'},
     *           'export_csv': {'sButtonText':'pi.grid.action.export'},
     *           'export_xls': {'sButtonText':'pi.grid.action.export'},
     *           'rows_text_test': {'sButtonText':'test', 'route':'sfynx_layout_enabledentity_ajax', 'questionTitle':'Titre de mon action', 'questionText':'Etes-vous sûr de vouloir activer toutes les lignes suivantes ?', 'typeResponse':'ajaxResult', 'responseText':'Operation successfully'},
     *           'rows_grouping': {'Collapsible':'false', 'GroupBy':'name', 'columnIndex':2, 'HideColumn':'true', 'SortDirection':'desc'},
     *           'rows_position': {'route':'sfynx_layout_position_ajax',
     *       }
     *     %}
     *     {{ renderJquery('GRID', 'grid-table', options_gridtabale )|raw }}
     *
     * </code>
     *
     * @access protected
     * @return void
     */
    protected function init($options = null)
    {
        // datatable core
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/media/js/jquery.dataTables.min.js");
        if ($this->container->hasParameter('sfynx.template.theme.layout.admin.grid.pagination.type')
            && ($this->container->getParameter('sfynx.template.theme.layout.admin.grid.pagination.type') == "bootstrap")
        ) {
            $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/media/js/DT_bootstrap.js");
            $this->container->get('sfynx.tool.twig.extension.layouthead')->addCssFile("bundles/sfynxtemplate/js/datatable/media/css/DT_bootstrap.css");
        }
        // plugin Reordering
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/plugins/RowReordering/jquery.dataTables.rowReordering.js");
        // plugin RowGrouping
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/plugins/RowGrouping/media/js/jquery.dataTables.rowGrouping.js");
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addCssFile("bundles/sfynxtemplate/js/datatable/plugins/RowGrouping/media/css/dataTables.rowGrouping.default.css", "append");
        // plugin Toolsrows_position
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/extras/TableTools/media/js/TableTools.min.js");
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/extras/TableTools/media/js/ZeroClipboard.js");
        // plugin ColumnFilterWidgets
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/plugins/ColumnFilterWidgets/media/js/ColumnFilterWidgets.js");
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addCssFile("bundles/sfynxtemplate/js/datatable/plugins/ColumnFilterWidgets/media/css/ColumnFilterWidgets.css");
        // plugin colreorder
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/plugins/ColReorderWithResize/ColReorderWithResize.js");
        // plugin ColVis
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/extras/ColVis/media/js/ColVis.min.js");
        // plugin Editor
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/datatable/plugins/Editor/js/dataTables.editor.js");
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addCssFile("bundles/sfynxtemplate/js/datatable/plugins/Editor/css/dataTables.editor.css", "append");
        // plugin fancybox for dialog box
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/fancybox/jquery.fancybox.pack.js");
        // spinner
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/spinner/spin.min.js");
        // simple and multiselect managment
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addCssFile("bundles/sfynxtemplate/js/jquery/multiselect/css/jquery.multiselect.filter.css");
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addCssFile("bundles/sfynxtemplate/js/jquery/multiselect/css/jquery.multiselect.css");
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/jquery/multiselect/js/jquery.multiselect.js");
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/jquery/multiselect/js/jquery.multiselect.filter.js");
        // multi-select chained management
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/jquery/jquery.chained.remote.js");
        // datepicker region
        $locale = strtolower(substr($this->request->getLocale(), 0, 2));
        $root_file = realpath($this->getProjectWebDir() . "bundles/sfynxtemplate/js/ui/i18n/jquery.ui.datepicker-{$locale}.js");
        if (!$root_file) {
            $locale = "en-GB";
        }
        $this->container->get('sfynx.tool.twig.extension.layouthead')->addJsFile("bundles/sfynxtemplate/js/ui/i18n/jquery.ui.datepicker-{$locale}.js");
        //http://datatables.net/forums/discussion/12443/scroller-extra-w-server-side-processing/p1
        //http://datatables.net/forums/discussion/14141/confirm-delete-on-tabletools/p1
    }

    /**
      * Sets the grid render.
      *
      * @param    $options    tableau d'options.
      * @access protected
      * @return void
      */
    protected function render($options = null)
    {
        // Options management
        if (!isset($options['grid-name']) || empty($options['grid-name'])) {
            throw ExtensionException::optionValueNotSpecified('grid-name', __CLASS__);
        }
        if (!isset($options['grid-type']) || empty($options['grid-type']) || (isset($options['grid-type']) && !in_array($options['grid-type'], self::$types))) {
            throw ExtensionException::optionValueNotSpecified('grid-type', __CLASS__);
        }
        if (!isset($options['grid-paginate']) || empty($options['grid-paginate'])) {
            $options['grid-paginate'] = true;
        }
        if ( $options['grid-type'] == "simple" ) {
            return $this->gridSimple($options);
        } elseif( $options['grid-type'] == "bootstrap" ) {
            return $this->gridBootstrap($options);
        }
    }

    /**
     * Sets the grid server render.
     *
     * @param    $options    tableau d'options.
     * @access private
     * @return string
     */
    protected function gridSimple($options = null)
    {
        //
        if (!isset($options['grid-paginate'])) { $options['grid-paginate'] = true; }
        if (!isset($options['grid-state-save'])) { $options['grid-state-save'] = false; }
        //
        $Urlpath      = $this->container->get('assets.packages')->getUrl("bundles/sfynxtemplate/js/datatable/extras/TableTools/media/swf/copy_csv_xls_pdf.swf");
        $Urlenabled   = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."enabled.png");
        $Urldisabled  = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."disabled.png");
        $remove       = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."remove.png");
        $select_all   = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."select_all.png");
        $select_none  = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."select_none.png");
        $print        = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."print.png");
        $export       = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."export.png");
        $export_pdf   = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."export_pdf.png");
        $export_csv   = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."export_csv.png");
        $export_xls   = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."export_xls.png");
        $copy         = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."copy.png");
        $export_all   = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."export_all.png");
        $archive      = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."archive.png");
        $penabled     = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."penabled.png");
        $pdisable     = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."pdisable.png");
        $action       = $this->container->get('assets.packages')->getUrl($this->container->getParameter('sfynx.template.theme.layout.admin.grid.img')."action.png");
        // we set the locale date format of datepicker
        $locale = strtolower(substr($this->request->getLocale(), 0, 2));
        $root_file    = realpath($this->getProjectWebDir() . "bundles/sfynxtemplate/js/ui/i18n/jquery.ui.datepicker-{$locale}.js");
        if (!$root_file) {
            $locale = "en-GB";
        }
        // set the csrf token
        $csrfToken = $this->securityManager->getToken('grid-action');
        // We open the buffer.
        ob_start ();
        ?>
                    function fnFilterGlobal ()
                    {
                        $('#<?php echo $options['grid-name']; ?>').dataTable().fnFilter(
                            $("#global_filter").val(),
                            null,
                            $("#global_regex")[0].checked,
                            $("#global_smart")[0].checked
                        );
                    }

                    function fnFilterColumn ( i )
                    {
                        $('#<?php echo $options['grid-name']; ?>').dataTable().fnFilter(
                            $("#col"+(i+1)+"_filter").val(),
                            i,
                            $("#col"+(i+1)+"_regex")[0].checked,
                            $("#col"+(i+1)+"_smart")[0].checked
                        );
                    }

                    function fnCreateFooterFilter()
                    {
                         <?php if(isset($options['grid-filters-tfoot-up']) && (($options['grid-filters-tfoot-up'] === 'true') || ($options['grid-filters-tfoot-up'] === true)) ) : ?>
                         $('tfoot tr').addClass("tfoot-up");
                         $('tfoot').replaceWith(function(){
                                return $("<thead />", {html: $(this).html()});
                         });
                         <?php endif; ?>
                        /* Add a select menu for each TH element in the table footer
                         *
                         *   <tfoot>
                         *      <tr>
                         *        <th data-type="input"><input type="text" name="" value="Position" style="width:100%" /></th>
                         *        <th data-type="input"><input type="text" name="" value="Id" style="width:100%" /></th>
                         *        <th data-column='2' data-title="{{ 'pi.form.label.field.topic'|trans }}"  data-ajaxsearch="false" data-values='{{ rubriques|json_encode }}'></th>
                         *        <th data-column='3' data-title="{{ 'pi.form.label.field.tag'|trans }}"></th>
                         *        <th data-column='4' data-type="input" ><input type="text" name="" value="" style="width:100%" /></th>
                         *        <th data-column='5' data-title="{{ 'pi.form.label.field.type'|trans }}" data-values='{"article":"Articles","diaporama":"Dossiers","test":"Tests","page":"Pages"}'></th>
                         *        <th data-column='6' data-title="{{ 'pi.form.label.field.author'|trans }}"></th>
                         *        <th data-column='7' data-title="{{ 'pi.page.form.status'|trans }}" data-values='{"Actif":"Actif","Archive":"Archivé","En attente dactivation":"En attente d activation"}'></th>
                         *        <th data-type="input"><input type="text" name="" value="createdat" style="width:100%" /></th>
                         *        <th data-type="input"><input type="text" name="" value="publishedat" style="width:100%" /></th>
                         *        <th data-type="input"><input type="text" name="" value="updatedat" style="width:100%" /></th>
                         *        <th></th>
                         *      </tr>
                         *  </tfoot>
                         */
                        $("table th").each( function ( i ) {
                                var column = $(this).data('column');
                                var values = $(this).data('values');
                                var type = $(this).data('type');
                                var title = $(this).data('title');
                                var ajaxsearch = $(this).data('ajaxsearch');
                                if (column != undefined) {
                                    <?php if(isset($options['grid-filter-date'])): ?>
                                        <?php foreach($options['grid-filter-date'] as $id => $gridDateFilter) : ?>
                                            var <?php echo $gridDateFilter['idMin']; ?>DateFilter;
                                            var <?php echo $gridDateFilter['idMax']; ?>DateFilter;
                                            $("table th").each( function ( i ) {
                                                var column = $(this).data('search');
                                                if (column == <?php echo $gridDateFilter['column']; ?>) {
                                                    $(this).html('<div id="filter-grid-date-<?php echo $id; ?>" ><input class="form-control form-control-inline input-medium default-date-picker" type="text" id="<?php echo $gridDateFilter['idMin']; ?>" name="<?php echo $gridDateFilter['idMin']; ?>"><input type="text" class="form-control form-control-inline input-medium default-date-picker" id="<?php echo $gridDateFilter['idMax']; ?>" name="<?php echo $gridDateFilter['idMax']; ?>"></div>');
                                                }
                                            });
                                            $("#<?php echo $gridDateFilter['idMax']; ?>").datepicker({
                                                changeMonth: true,
                                                changeYear: true,
                                                yearRange: "-71:+11",
                                                reverseYearRange: true,
                                                showOtherMonths: true,
                                                showButtonPanel: true,
                                                showAnim: "fade",  // blind fade explode puff fold
                                                showWeek: true,
                                                format: "<?php echo $gridDateFilter['format']; ?>",
                                                dateFormat: "<?php echo $gridDateFilter['format']; ?>",
                                                showOptions: {
                                                    direction: "up"
                                                },
                                                numberOfMonths: [ 1, 2 ],
                                                buttonText: "<?php echo $this->translator->trans('pi.form.label.select.choose.date'); ?>",
                                                showOn: "focus",
                                                buttonImage: "/bundles/sfynxtemplate/images/icons/form/picto-calendar.png",
                                                onSelect: function(date) {
                                                    <?php echo $gridDateFilter['idMax']; ?>DateFilter = new Date(date).getTime();
                                                    <?php echo $options['grid-name']; ?>oTable.fnDraw();
                                                    $(this).datepicker('hide');
                                                }
                                            }).keyup( function () {
                                                <?php echo $gridDateFilter['idMax']; ?>DateFilter = new Date(this.value).getTime();
                                                <?php echo $options['grid-name']; ?>oTable.fnDraw();
                                                $(this).datepicker('hide');
                                            }).on('changeDate', function(ev){
                                                <?php echo $gridDateFilter['idMax']; ?>DateFilter = new Date(ev.date).getTime();
                                                <?php echo $options['grid-name']; ?>oTable.fnDraw();
                                                $(this).datepicker('hide');
                                            });
                                            $("#<?php echo $gridDateFilter['idMin']; ?>").datepicker({
                                                changeMonth: true,
                                                changeYear: true,
                                                yearRange: "-71:+11",
                                                reverseYearRange: true,
                                                showOtherMonths: true,
                                                showButtonPanel: true,
                                                showAnim: "fade",  // blind fade explode puff fold
                                                showWeek: true,
                                                format: "<?php echo $gridDateFilter['format']; ?>",
                                                dateFormat: "<?php echo $gridDateFilter['format']; ?>",
                                                showOptions: {
                                                    direction: "up"
                                                },
                                                numberOfMonths: [ 1, 2 ],
                                                buttonText: "<?php echo $this->translator->trans('pi.form.label.select.choose.date'); ?>",
                                                showOn: "focus",
                                                buttonImage: "/bundles/sfynxtemplate/images/icons/form/picto-calendar.png",
                                                onSelect: function(date) {
                                                    <?php echo $gridDateFilter['idMin']; ?>DateFilter = new Date(date).getTime();
                                                    <?php echo $options['grid-name']; ?>oTable.fnDraw();
                                                    $(this).datepicker('hide');
                                                  }
                                            }).keyup( function () {
                                                <?php echo $gridDateFilter['idMin']; ?>DateFilter = new Date(this.value).getTime();
                                                <?php echo $options['grid-name']; ?>oTable.fnDraw();
                                                $(this).datepicker('hide');
                                            }).on('changeDate', function(ev){
                                                <!-- http://bootstrap-datepicker.readthedocs.org/en/release/methods.html#setdate -->
                                                <?php echo $gridDateFilter['idMax']; ?>DateFilter = new Date(ev.date).getTime();
                                                <?php echo $options['grid-name']; ?>oTable.fnDraw();
                                                $(this).datepicker('hide');
                                            });
                                            $.datepicker.setDefaults( $.datepicker.regional[ "<?php echo $locale; ?>" ] );
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    if (values == undefined) {
                                        values = <?php echo $options['grid-name']; ?>oTable.fnGetColumnData(column)
                                    }
                                    if (type != "input") {
                                        var options = [];
                                        $('select', this).find(':selected').each(function(j,v){
                                            options[j] = $(v).val();
                                        });
                                        $(this).html( fnCreateSelect( values, title, i) );
                                        $('select', this).data('title', title).data('column', column).val(options);

                                        $('select', this).change( function () {
                                            var values = $("#select_"+i).val().join('|');
                                            <?php echo $options['grid-name']; ?>oTable.fnFilter( values, column, true );
                                        });
                                        $("#select_"+i).multiselect({
                                            multiple: true,
                                            header: true,
                                            noneSelectedText: title,
                                            create: function(){ $(this).next().width('auto');$(this).multiselect("widget").width('auto'); },
                                            open: function(){ $(this).next().width('auto');$(this).multiselect("widget").width('auto'); },
                                        }).multiselectfilter({
                                            filter: function(e, matches) {
                                                e.preventDefault();
                                                var keyword = $('.ui-multiselect-filter:visible').find('input').val();
                                                if ( (ajaxsearch === true) || (ajaxsearch === 'true') ) {
                                                    if(keyword != $(e.target).data('keyword')) {
                                                        <?php echo $options['grid-name']; ?>oTable.fnFilter( keyword, column, true );
                                                    }
                                                }
                                            }
                                        });
                                    } else {
                                        var search_timeout = undefined;
                                        $(this).find('input').width('91%').attr('id', 'input_'+i).data('title', title).data('column', column).keyup( function () {
                                            if(search_timeout != undefined) {
                                                clearTimeout(search_timeout);
                                            }
                                            $this = this;
                                            search_timeout = setTimeout(function() {
                                              search_timeout = undefined;
                                              <?php echo $options['grid-name']; ?>oTable.fnFilter( $this.value, column, true );
                                            }, 1000);
                                        } );
                                    }
                                } else if (type != undefined) {
                                    this.innerHTML = '' ;
                                }
                        });
/*
                        $("[id^='select_']").change( function () {
                            var values = $(this).val().join('|');
                            <?php echo $options['grid-name']; ?>oTable.fnFilter( values, $(this).data('column'), true );
                        });
                        $("[id^='select_']").multiselect({
                            multiple: true,
                            header: true,
                            noneSelectedText: $(this).data('title'),
                            create: function(){ $(this).next().width('auto');$(this).multiselect("widget").width('auto'); },
                            open: function(){ $(this).next().width('auto');$(this).multiselect("widget").width('auto'); },
                        }).multiselectfilter({
                            filter: function(e, matches) {
                                e.preventDefault();
                                var keyword = $('.ui-multiselect-filter:visible').find('input').val();
                                if(keyword != $(e.target).data('keyword')) {
                                    <?php echo $options['grid-name']; ?>oTable.fnFilter( keyword, $(this).data('column'), true );
                                }
                            }
                        });
                        var foo_input = function() {
                            <?php echo $options['grid-name']; ?>oTable.fnFilter( $(this).val(), $(this).data('column'), true );
                        };
                        $("[id^='input_']").off("keyup", foo_input);
                        $("[id^='input_']").on("keyup", foo_input);
*/
                        $("[id^='ui-multiselect-']").each(function(i){
                            var string = $(this).next('span').html();
                            string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
                            string = string.replace(/&#0*39;/g, "'");
                            string = string.replace(/&quot;/g, '"');
                            string = string.replace(/&amp;/g, '&');
                            $(this).next('span').html(string);
                            $(this).click(function() {
                                    var id = $(this).attr('id').toString().replace(/-option-(.+)/ig,'').replace('ui-multiselect-','');
                                    var string = $(this).val();
                                    string = string.toString().replace(/&amp;lt;img.*?\/&amp;gt;/ig,'');
                                    $("#"+id).next("button.ui-multiselect").html(string);
                            });
                        });
                    }

                    function fnCreateSelect( aData, title, myColumnID)
                    {
                        var mySelectID = 'select_' + myColumnID;
                        var options = $("<select id='"+mySelectID+"' name='"+mySelectID+"' class='filtSelect' style='width:auto' multiple='multiple'  />"),
                        addOptions = function(opts, container){
                            container.append($("<option />").val('').text('<?php echo $this->translator->trans('pi.page.All'); ?>'));
                            $.each(opts, function(i, opt) {
                                if(typeof(opt)=='string'){
                                    if(typeof(i)=='string'){
                                    container.append($("<option />").val(i).text(opt));
                                    }else{
                                        container.append($("<option />").val(opt).text(opt));
                                    }
                                } else {
                                    var optgr = $("<optgroup />").attr('label',i);
                                    addOptions(opt, optgr)
                                    container.append(optgr);
                                }
                            });
                        };

                        options.css('width', '100%')
                        addOptions(aData,options);
                        return options;
                    }

                    $.extend( $.fn.dataTableExt.oSort, {
                        "num-html-pre": function ( a ) {
                            var x = a.replace( /<.*?>/g, "" );
                            x = x.replace( "%", "" );
                            if(x == " ") { x=-1; }
                            return parseFloat( x );
                        },
                        "num-html-asc": function ( a, b ) {
                            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
                        },

                        "num-html-desc": function ( a, b ) {
                            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
                        }
                    } );

                    $.fn.dataTableExt.oSort['numeric-comma-asc']  = function(a,b) {
                        var x = (a == "-") ? 0 : a.replace( /,/, "." );
                        var y = (b == "-") ? 0 : b.replace( /,/, "." );
                        x = parseFloat( x );
                        y = parseFloat( y );
                        return ((x < y) ? -1 : ((x > y) ?  1 : 0));
                    };

                    $.fn.dataTableExt.oSort['numeric-comma-desc'] = function(a,b) {
                        var x = (a == "-") ? 0 : a.replace( /,/, "." );
                        var y = (b == "-") ? 0 : b.replace( /,/, "." );
                        x = parseFloat( x );
                        y = parseFloat( y );
                        return ((x < y) ?  1 : ((x > y) ? -1 : 0));
                    };

                    (function($) {
                        /*
                         * Function: fnGetColumnData
                         * Purpose:  Return an array of table values from a particular column.
                         * Returns:  array string: 1d data array
                         * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
                         *           int:iColumn - the id of the column to extract the data from
                         *           bool:bUnique - optional - if set to false duplicated values are not filtered out
                         *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
                         *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
                         * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
                         */
                        $.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
                            // check that we have a column id
                            if ( typeof iColumn == "undefined" ) return new Array();

                            // by default we only want unique data
                            if ( typeof bUnique == "undefined" ) bUnique = true;

                            // by default we do want to only look at filtered data
                            if ( typeof bFiltered == "undefined" ) bFiltered = true;

                            // by default we do not want to include empty values
                            if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;

                            // list of rows which we're going to loop through
                            var aiRows;

                            // use only filtered rows
                            if (bFiltered == true) aiRows = oSettings.aiDisplay;
                            // use all rows
                            else aiRows = oSettings.aiDisplayMaster; // all row numbers

                            // set up data array
                            var asResultData = new Array();

                            for (var i=0,c=aiRows.length; i<c; i++) {
                                iRow = aiRows[i];
                                var aData = this.fnGetData(iRow);
                                var sValue = aData[iColumn];

                                // Error lorsque sValue = null
                                if(sValue == null) continue;

                                // ignore empty values?
                                else if (bIgnoreEmpty == true && sValue.length == 0) continue;

                                // ignore unique values?
                                else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;

                                // else push the value onto the result data array
                                else asResultData.push(sValue);
                            }

                            return asResultData;
                    }}(jQuery));


                    <?php if(isset($options['grid-filter-date'])): ?>
                        <?php foreach ($options['grid-filter-date'] as $id => $gridDateFilter) { ?>
                            // http://live.datatables.net/etewoq/4/edit#javascript,html,live
                            var <?php echo $gridDateFilter['idMin']; ?>DateFilter;
                            var <?php echo $gridDateFilter['idMax']; ?>DateFilter;
                            <?php if(isset($options['grid-server-side']) && (($options['grid-server-side'] == 'true') || ($options['grid-server-side'] == true)) ) : ?>
                            <?php else: ?>
                            $.fn.dataTableExt.afnFiltering.push (
                                  function( oSettings, aData, iDataIndex ) {
                                        if ( typeof aData._date == 'undefined' ) {
                                          aData._date = new Date(aData["<?php echo $gridDateFilter['column']; ?>"]).getTime();
                                        }
                                        if ( <?php echo $gridDateFilter['idMin']; ?>DateFilter && !isNaN(<?php echo $gridDateFilter['idMin']; ?>DateFilter) ) {
                                          if ( aData._date < <?php echo $gridDateFilter['idMin']; ?>DateFilter ) {
                                            return false;
                                          }
                                        }
                                        if ( <?php echo $gridDateFilter['idMax']; ?>DateFilter && !isNaN(<?php echo $gridDateFilter['idMax']; ?>DateFilter) ) {
                                          if ( aData._date > <?php echo $gridDateFilter['idMax']; ?>DateFilter ) {
                                            return false;
                                          }
                                        }
                                        return true;
                                  }
                            );
                            <?php endif; ?>
                        <?php } ?>
                    <?php endif; ?>

                    var enabled;
                    var disablerow;
                    var deleterow;
                    var archiverow;

                    var <?php echo $options['grid-name']; ?>oTable;
                    var envelopeConf = $.fn.dataTable.Editor.display.envelope.conf;
                    envelopeConf.attach = 'head';
                    envelopeConf.windowScroll = false;

                    $('*[class^="button-ui"]').each( function ( i ) {
                        var name_button = $(this).data('ui-icon');
                        var class_button = $(this).attr('class');
                        $("a.button-"+name_button).button({icons: {primary: name_button}});
                        <?php
                                if (
                                    $this->container->hasParameter('sfynx.template.theme.layout.admin.grid.pagination.type')
                                    &&
                                    ( $this->container->getParameter('sfynx.template.theme.layout.admin.grid.pagination.type') == "bootstrap")
                                ) {
                        ?>
                        $(this).attr('class', class_button + " ui-icon  " + name_button);
                        <?php } ?>
                    });

                    $("td.enabled").each(function(index) {
                        var value = $(this).html();
                        if (value == 1)
                            $(this).html('<img width="17px" src="<?php echo $Urlenabled ?>">');
                        if (value == 0)
                            $(this).html('<img width="17px" src="<?php echo $Urldisabled ?>">');
                    });

                    $('#<?php echo $options['grid-name']; ?> tbody tr').each(function(index) {
                        $(this).find("td.position").prependTo(this);
                    });
                    $('#<?php echo $options['grid-name']; ?> thead tr').each(function(index) {
                        $(this).find("th.position").prependTo(this);
                    });

                    /* Add the events etc before DataTables hides a column  Filter on the column (the index) of this element
                    $("tfooter input").keyup( function () {
                        <?php echo $options['grid-name']; ?>oTable.fnFilter( this.value, <?php echo $options['grid-name']; ?>oTable.oApi._fnVisibleToColumnIndex(
                                <?php echo $options['grid-name']; ?>oTable.fnSettings(), $("thead input").index(this) ) );
                    } );
                    */

                    <?php if (isset($options['grid-actions']) && !empty($options['grid-actions']) && \is_array($options['grid-actions'])): ?>
                        <?php foreach($options['grid-actions'] as $actionName => $params): ?>
                            <?php if ( ($actionName == "rows_enabled") && isset($params['route']) && !empty($params['route']) ) : ?>
                            // Set up enabled row
                            enabled = new $.fn.dataTable.Editor( {
                                "domTable": "#<?php echo $options['grid-name']; ?>",
                                //"display": "envelope",
                                "ajaxUrl": "<?php echo $this->container->get('router')->generate($params['route']) ?>?_token=<?php echo $csrfToken; ?>",
                                "events": {
                                    "onPreSubmit": function (data) {
                                    },
                                    "onPostSubmit": function (json, data) {
                                    },
                                    "onPreRemove": function (json) {
                                    },
                                    "onPostRemove": function (json) {
                                    }
                                }
                            } );
                            <?php elseif ( ($actionName == "rows_disable") && isset($params['route']) && !empty($params['route']) ): ?>
                            // Set up disable row
                            disablerow = new $.fn.dataTable.Editor( {
                                "domTable": "#<?php echo $options['grid-name']; ?>",
                                //"display": "envelope",
                                "ajaxUrl": "<?php echo $this->container->get('router')->generate($params['route']) ?>?_token=<?php echo $csrfToken; ?>",
                                "events": {
                                    "onPreSubmit": function (data) {
                                    },
                                    "onPostSubmit": function (json, data) {
                                    },
                                    "onPreRemove": function (json) {
                                    },
                                    "onPostRemove": function (json) {
                                    }
                                }
                            } );
                            <?php elseif ( ($actionName == "rows_delete") && isset($params['route']) && !empty($params['route']) ): ?>
                            // Set up delete row
                            deleterow = new $.fn.dataTable.Editor( {
                                "domTable": "#<?php echo $options['grid-name']; ?>",
                                //"display": "envelope",
                                "ajaxUrl": "<?php echo $this->container->get('router')->generate($params['route']) ?>?_token=<?php echo $csrfToken; ?>"
                            } );
                            <?php elseif ( ($actionName == "rows_archive") && isset($params['route']) && !empty($params['route']) ): ?>
                            // Set up archive row
                            archiverow = new $.fn.dataTable.Editor( {
                                "domTable": "#<?php echo $options['grid-name']; ?>",
                                //"display": "envelope",
                                "ajaxUrl": "<?php echo $this->container->get('router')->generate($params['route']) ?>?_token=<?php echo $csrfToken; ?>"
                            } );
                            <?php elseif ( !empty($actionName) && (strstr($actionName, 'rows_default_') != "") && isset($params['route']) && !empty($params['route']) ): ?>
                            // Set up archive row
                            defaultrow_<?php echo $actionName; ?> = new $.fn.dataTable.Editor( {
                                "domTable": "#<?php echo $options['grid-name']; ?>",
                                //"display": "envelope",fnServerData
                                "ajaxUrl": "<?php echo $this->container->get('router')->generate($params['route']) ?>?_token=<?php echo $csrfToken; ?>"
                            } );
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php echo $options['grid-name']; ?>oTable = $('#<?php echo $options['grid-name']; ?>').dataTable({
                        "bPaginate":<?php echo ((int) $options['grid-paginate']  || ($options['grid-paginate'] === 'true')) ? 'true' : 'false'; ?>,
                        "bRetrieve":true,
                        "bFilter": true,
                        <?php if(isset($options['grid-pagination-type']) && !empty($options['grid-pagination-type'])) : ?>
                        "sPaginationType": "<?php echo $options['grid-pagination-type']; ?>",
                        <?php else: ?>
                        "sPaginationType": "full_numbers",
                        <?php endif; ?>
                        "bJQueryUI":true,
                        "bAutoWidth": false,
                        "bProcessing": true,
                        "bStateSave": <?php echo ((int) $options['grid-state-save']  || ($options['grid-state-save'] === 'true')) ? 'true' : 'false'; ?>,
                        "fnInitComplete": function(oSettings, json) {
                          //
                        },
                        <?php if(isset($options['grid-server-side']) && (($options['grid-server-side'] === 'true') || ($options['grid-server-side'] === true)) ) : ?>
                        "bServerSide": true,
                        "sAjaxSource": "<?php echo $this->container->get('request_stack')->getCurrentRequest()->getRequestUri(); ?>",
                        'fnServerData' : function ( sSource, aoData, fnCallback ) {
                            <?php if (isset($options['grid-filter-date'])): ?>
                                <?php foreach ($options['grid-filter-date'] as $id => $gridDateFilter) { ?>
                                aoData.push( { 'name' : 'date-<?php echo $gridDateFilter['idMin']; ?>', 'value' : $("#<?php echo $gridDateFilter['idMin']; ?>").val() } );
                                aoData.push( { 'name' : 'date-<?php echo $gridDateFilter['idMax']; ?>', 'value' : $("#<?php echo $gridDateFilter['idMax']; ?>").val() } );
                                <?php } ?>
                            <?php endif; ?>

                            //$.getJSON( sSource, aoData, function (json) {
                                /* Do whatever additional processing you want on the callback, then tell DataTables */
                            //    fnCallback(json)
                            //} );
                            $.ajax({
                                'dataType' : 'json',
                                'data' : aoData,
                                'type' : 'GET',
                                'url' : sSource,
                                'success' : fnCallback
                            });
                        },
                        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
                            /* Append the grade to the default row class name */
                            var id = aData[0];
                            $(nRow).attr("id",id);
                            return nRow;
                        },
                        "fnInfoCallback": function( oSettings, iStart, iEnd, iMax, iTotal, sPre ) {
                            $("a.info-tooltip").tooltip({
                                position: {
                                    track: true,
                                    my: "center bottom-20",
                                    at: "center top",
                                  },
                                content: function () {
                                      return $(this).prop('title');
                                  }
                            });

                            $('*[class^="button-ui"]').each( function ( i ) {
                                var name_button = $(this).data('ui-icon');
                                var class_button = $(this).attr('class');
                                $("a.button-"+name_button).button({icons: {primary: name_button}});
                                <?php
                                        if (
                                            $this->container->hasParameter('sfynx.template.theme.layout.admin.grid.pagination.type')
                                            &&
                                            ( $this->container->getParameter('sfynx.template.theme.layout.admin.grid.pagination.type') == "bootstrap")
                                        ) {
                                ?>
                                $(this).attr('class', class_button + " ui-icon  " + name_button);
                                <?php } ?>
                            });

                            /* Add a select menu for each TH element in the table footer */
                            /* http://datatables.net/forums/discussion/comment/33095 */

                            fnCreateFooterFilter()
                        },
                        <?php endif; ?>

                        <?php if (isset($options['grid-sorting']) && !empty($options['grid-sorting']) && \is_array($options['grid-sorting'])): ?>
                        "aaSorting":
                            [
                                <?php foreach($options['grid-sorting'] as $id => $odrer): ?>
                                    [<?php echo $id; ?>,'<?php echo $odrer; ?>'],
                                <?php endforeach; ?>
                            ],
                        <?php endif; ?>

                        <?php if (isset($options['grid-columns']) && !empty($options['grid-columns']) && \is_array($options['grid-columns'])): ?>
                        "aoColumns":
                            [
                               <?php foreach($options['grid-columns'] as $key => $value): ?>
                                    <?php echo json_encode($value, true); ?>,
                                <?php endforeach; ?>
                            ],
                        <?php endif; ?>

                        "aLengthMenu": [[1, 5, 10, 15, 20, 25, 50, 100, 500, 1000, 5000 -1], [1, 5, 10, 15, 20, 25, 50, 100, 500, 1000, 5000, "All"]],
                        <?php if ( isset($options['grid-LengthMenu']) && !empty($options['grid-LengthMenu']) ): ?>
                        "iDisplayLength": <?php echo $options['grid-LengthMenu']; ?>,
                        <?php else: ?>
                        "iDisplayLength": 25,
                        <?php endif; ?>

                        "oLanguage": {
                            "sLoadingRecords": "<div id='spin'></div><?php echo $this->translator->trans('pi.grid.action.waiting'); ?>",
                            "sProcessing": "<div id='spin' style='display:block;width:24px;height:24px;float:left;margin: 6px 2px;'></div><?php echo $this->translator->trans('pi.grid.action.waiting'); ?>",
                            "sLengthMenu": "<?php echo $this->translator->trans('pi.grid.action.lenghtmenu'); ?>",
                            "sZeroRecords": "Nothing found - sorry",
                            "sInfo": "<?php echo $this->translator->trans('pi.grid.action.info'); ?>",
                            "sInfoEmpty": "<?php echo $this->translator->trans('pi.grid.action.info.empty'); ?>",
                            "sInfoFiltered": "<?php echo $this->translator->trans('pi.grid.action.info.filtered'); ?>",
                            "sInfoPostFix": "",
                            "sSearch": "<?php echo $this->translator->trans('pi.grid.action.search'); ?>",
                            "sUrl": "",
                            "oPaginate": {
                                "sFirst":    "<?php echo $this->translator->trans('pi.grid.action.first'); ?>",
                                "sPrevious": "<?php echo $this->translator->trans('pi.grid.action.previous'); ?>",
                                "sNext":     "<?php echo $this->translator->trans('pi.grid.action.next'); ?>",
                                "sLast":     "<?php echo $this->translator->trans('pi.grid.action.last'); ?>"
                            }
                        },
                        // l - Length changing
                        // f - Filtering input
                        // t - The table!
                        // i - Information
                        // p - Pagination
                        // r - pRocessing
                        // < and > - div elements
                        // <"class" and > - div with a class
                        // Examples: <"wrapper"flipt>, <lf<t>ip>
                        //avec multi-filtre : "sDom": '<"block_filter"><"H"RTfr<"clear"><?php if (isset($options["grid-filters-select"])) { echo "W"; } ?>>tC<"F"lpi>',
                        <?php if (isset($options['grid-filters']) && isset($options['grid-filters-active']) && (($options['grid-filters-active'] === 'true') || ($options['grid-filters-active'] === true)) ) : ?>
                            <?php if ((isset($options['grid-paginate-top']) && (($options['grid-paginate-top'] === 'false') || ($options['grid-paginate-top'] === false))) ) : ?>
                            "sDom": '<"block_filter"><"H"RTfr<"clear"><?php if(isset($options["grid-filters-select"])) { echo "W"; } ?>>tC<"F"lpi>',
                            <?php else: ?>
                            "sDom": '<"block_filter"><"H"RTfr<"clear"><?php if(isset($options["grid-filters-select"])) { echo "W"; } ?><"clear">p<"clear">>tC<"F"lpi>',
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ((isset($options['grid-paginate-top']) && (($options['grid-paginate-top'] === 'false') || ($options['grid-paginate-top'] === false))) ) : ?>
                            "sDom": '<"H"RTfr<"clear"><?php if (isset($options["grid-filters-select"])) { echo "W"; } ?>>tC<"F"lpi>',
                            <?php else: ?>
                            "sDom": '<"H"RTfr<"clear"><?php if (isset($options["grid-filters-select"])) { echo "W"; } ?><"clear">p<"clear">>tC<"F"lpi>',
                            <?php endif; ?>
                        <?php endif; ?>

                        "oTableTools": {
                            "sSwfPath": "<?php echo $Urlpath; ?>",
                            "sRowSelect": "<?php if (isset($options['grid-row-select']) && !empty($options['grid-row-select'])): ?><?php echo $options['grid-row-select']; ?><?php else: ?>multi<?php endif; ?>",       //  ['single', 'multi']
                            "aButtons": [
                        <?php if (isset($options['grid-actions']) && !empty($options['grid-actions']) && \is_array($options['grid-actions'])): ?>
                            <?php foreach($options['grid-actions'] as $actionName => $params): ?>
                                    <?php if ($actionName == "rows_enabled"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.rows_enabled'; ?>
                                            {
                                                "sExtends": "editor_remove",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $penabled ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "editor": enabled,
                                                "formButtons": [
                                                    {
                                                        "label": "Valider",
                                                        "className": "save",
                                                        "fn": function (e) {
                                                            this.submit(function(){
                                                                <?php if (isset($params['reload']) && ($params['reload'] == 1) ) : ?>
                                                                window.location.reload();
                                                                <?php endif; ?>
                                                            });
                                                            $("tr.DTTT_selected td.enabled").html('<img width="17px" src="<?php echo $Urlenabled ?>">');
                                                        }
                                                    }
                                                ],
                                                <?php if (!isset($params['questionTitle']) || empty($params['questionTitle']) ) : ?>
                                                "formTitle": "Activer données",
                                                  <?php else: ?>
                                                "formTitle": "<?php echo $this->translator->trans($params['questionTitle']); ?>",
                                                  <?php endif; ?>
                                                "question": function(b) {
                                                  <?php if (!isset($params['questionText']) || empty($params['questionText']) ) : ?>
                                                    return "Voulez-vous activer " + b + " ligne" + (b === 1 ? " ?" : "s ?")
                                                      <?php else: ?>
                                                    return "<?php echo $this->translator->trans($params['questionText']); ?>"
                                                      <?php endif; ?>
                                                },
                                            },
                                    <?php elseif ($actionName == "rows_disable"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.rows_disable'; ?>
                                            {
                                                "sExtends": "editor_remove",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $pdisable ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "editor": disablerow,
                                                "formButtons": [
                                                    {
                                                        "label": "Valider",
                                                        "className": "save",
                                                        "fn": function (e) {
                                                            this.submit(function(){
                                                                <?php if (isset($params['reload']) && ($params['reload'] == 1) ) : ?>
                                                                window.location.reload();
                                                                <?php endif; ?>
                                                            });
                                                            $("tr.DTTT_selected td.enabled").html('<img width="17px" src="<?php echo $Urldisabled ?>">');
                                                        }
                                                    }
                                                ],
                                                <?php if (!isset($params['questionTitle']) || empty($params['questionTitle']) ) : ?>
                                                "formTitle": "Désactiver données",
                                                  <?php else: ?>
                                                "formTitle": "<?php echo $this->translator->trans($params['questionTitle']); ?>",
                                                  <?php endif; ?>
                                                "question": function(b) {
                                                  <?php if (!isset($params['questionText']) || empty($params['questionText']) ) : ?>
                                                    return "Voulez-vous désactiver " + b + " ligne" + (b === 1 ? " ?" : "s ?")
                                                      <?php else: ?>
                                                    return "<?php echo $this->translator->trans($params['questionText']); ?>"
                                                      <?php endif; ?>
                                                }
                                            },
                                    <?php elseif ($actionName == "rows_delete"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.row_delete'; ?>
                                            {
                                                "sExtends": "editor_remove",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $remove; ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "editor": deleterow,
                                                "formButtons": [
                                                    {
                                                        "label": "Valider",
                                                        "className": "save",
                                                        "fn": function (e) {
                                                            this.submit(function(){
                                                                <?php if (isset($params['reload']) && ($params['reload'] == 1) ) : ?>
                                                                window.location.reload();
                                                                <?php endif; ?>

                                                                <?php if (isset($params['remove']) && ($params['remove'] == 1) ) : ?>
                                                                $("tr.DTTT_selected td").remove();
                                                                <?php endif; ?>
                                                            });
                                                        },
                                                    }
                                                ],
                                                <?php if (!isset($params['questionTitle']) || empty($params['questionTitle']) ) : ?>
                                                "formTitle": "Suppression de données",
                                                  <?php else: ?>
                                                "formTitle": "<?php echo $this->translator->trans($params['questionTitle']); ?>",
                                                  <?php endif; ?>
                                                "question": function(b) {
                                                  <?php if (!isset($params['questionText']) || empty($params['questionText']) ) : ?>
                                                    return "Voulez-vous supprimer " + b + " ligne" + (b === 1 ? " ?" : "s ?")
                                                      <?php else: ?>
                                                    return "<?php echo $this->translator->trans($params['questionText']); ?>"
                                                      <?php endif; ?>
                                                }
                                            },
                                    <?php elseif ($actionName == "rows_archive"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.row_archive'; ?>
                                            {
                                                "sExtends": "editor_remove",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $archive ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "editor": archiverow,
                                                "formButtons": [
                                                    {
                                                        "label": "Valider",
                                                        "className": "save",
                                                        "fn": function (e) {
                                                            this.submit(function(){
                                                                <?php if (isset($params['reload']) && ($params['reload'] == 1) ) : ?>
                                                                window.location.reload();
                                                                <?php endif; ?>

                                                                <?php if (isset($params['remove']) && ($params['remove'] == 1) ) : ?>
                                                                $("tr.DTTT_selected td").remove();
                                                                <?php endif; ?>
                                                            });
                                                        },
                                                    }
                                                ],
                                                <?php if (!isset($params['questionTitle']) || empty($params['questionTitle']) ) : ?>
                                                "formTitle": "Archiver données",
                                                  <?php else: ?>
                                                "formTitle": "<?php echo $this->translator->trans($params['questionTitle']); ?>",
                                                  <?php endif; ?>
                                                "question": function(b) {
                                                  <?php if (!isset($params['questionText']) || empty($params['questionText']) ) : ?>
                                                    return "Voulez-vous archiver " + b + " ligne" + (b === 1 ? " ?" : "s ?")
                                                      <?php else: ?>
                                                    return "<?php echo $this->translator->trans($params['questionText']); ?>"
                                                      <?php endif; ?>
                                                }
                                            },
                                    <?php elseif ($actionName == "select_all"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.select_all'; ?>
                                            {
                                                "sExtends": "select_all",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $select_all ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "fnComplete": function ( nButton, oConfig, oFlash, sFlash ) {
                                                    $("input[type=checkbox]").prop('checked', false);
                                                },
                                            },
                                    <?php elseif ($actionName == "select_none"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.select_none'; ?>
                                            {
                                                "sExtends": "select_none",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $select_none ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "fnComplete": function ( nButton, oConfig, oFlash, sFlash ) {
                                                    $("input[type=checkbox]").prop('checked', false);
                                                },
                                            },
                                    <?php elseif ($actionName == "copy"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.copy'; ?>
                                            {
                                                "sExtends": "copy",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $copy ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                            },
                                    <?php elseif ($actionName == "print"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.print'; ?>
                                            {
                                                "sExtends": "print",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $print; ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                            },
                                    <?php elseif ($actionName == "export"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.export'; ?>
                                            <?php if(!isset($params['sTitle']) || empty($params['sTitle']) ) $params['sTitle'] = 'Sfynx'; ?>
                                            {
                                                "sExtends":    "collection",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $export_all ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  /><?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "aButtons":    [
                                                     {
                                                         "sExtends": "csv",
                                                         "sTitle": "<?php echo $this->translator->trans($params['sTitle']); ?>"
                                                     },
                                                     {
                                                         "sExtends": "xls",
                                                         "sTitle": "<?php echo $this->translator->trans($params['sTitle']); ?>"
                                                     },
                                                     {
                                                        "sExtends": "pdf",
                                                        "sPdfOrientation": "landscape",
                                                        "sPdfMessage": "PDF export (<?php echo date("Y/m/d"); ?>)",
                                                        "sTitle": "<?php echo $this->translator->trans($params['sTitle']); ?>"
                                                     }
                                                 ]
                                            },
                                    <?php elseif ($actionName == "export_csv"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.export.csv'; ?>
                                            <?php if(!isset($params['sTitle']) || empty($params['sTitle']) ) $params['sTitle'] = 'Sfynx'; ?>
                                            {
                                                "sExtends": "csv",
                                                "sTitle": "<?php echo $this->translator->trans($params['sTitle']); ?>",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $export_csv; ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  />CSV"
                                            },
                                    <?php elseif ($actionName == "export_pdf"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.export.pdf'; ?>
                                            <?php if(!isset($params['sTitle']) || empty($params['sTitle']) ) $params['sTitle'] = 'Sfynx'; ?>
                                            {
                                                "sExtends": "pdf",
                                                "sTitle": "<?php echo $this->translator->trans($params['sTitle']); ?>",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $export_pdf; ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  />PDF",
                                                "sPdfOrientation": "landscape",
                                                "sPdfMessage": "PDF export (<?php echo date("Y/m/d"); ?>)"
                                            },
                                    <?php elseif ($actionName == "export_xls"): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = 'pi.grid.action.export.xls'; ?>
                                            <?php if(!isset($params['sTitle']) || empty($params['sTitle']) ) $params['sTitle'] = 'Sfynx'; ?>
                                            {
                                                "sExtends": "xls",
                                                "sTitle": "<?php echo $this->translator->trans($params['sTitle']); ?>",
                                                "sButtonText": "<img class='btn-action' src='<?php echo $export_xls; ?>' title='<?php echo $this->translator->trans($params['sButtonText']); ?>' alt='<?php echo $this->translator->trans($params['sButtonText']); ?>'  />XLS"
                                            },
                                    <?php elseif (!empty($actionName) && (strstr($actionName, 'rows_default_') != "") ): ?>
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = '_new_'; ?>
                                            {
                                                "sExtends": "editor_remove",
                                                "sButtonText": "<?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "editor": defaultrow_<?php echo $actionName; ?>,
                                                "formButtons": [
                                                    {
                                                        "label": "Valider",
                                                        "className": "save",
                                                        "fn": function (e) {
                                                            this.submit(function(){
                                                                <?php if (isset($params['reload']) && ($params['reload'] == 1) ) : ?>
                                                                window.location.reload();
                                                                <?php endif; ?>

                                                                <?php if (isset($params['remove']) && ($params['remove'] == 1) ) : ?>
                                                                $("tr.DTTT_selected td").remove();
                                                                <?php endif; ?>

                                                                <?php if (isset($params['withImg']) && ($params['withImg'] == 1) ) : ?>
                                                                $("tr.DTTT_selected td.enabled").html('<img width="17px" src="<?php echo $Urlenabled ?>">');
                                                                <?php endif; ?>
                                                            });
                                                        },
                                                    }
                                                ],
                                                <?php if (!isset($params['questionTitle']) || empty($params['questionTitle']) ) : ?>
                                                "formTitle": "",
                                                  <?php else: ?>
                                                "formTitle": "<?php echo $this->translator->trans($params['questionTitle']); ?>",
                                                  <?php endif; ?>
                                                "question": function(b) {
                                                  <?php if (!isset($params['questionText']) || empty($params['questionText']) ) : ?>
                                                    return ""
                                                      <?php else: ?>
                                                      return "<?php echo $this->translator->trans($params['questionText']); ?>"
                                                      <?php endif; ?>
                                                }
                                            },
                                    <?php elseif (!empty($actionName) && (strstr($actionName, 'rows_text_') != "") ): ?>
                                    // exemple : 'rows_text_test': {'sButtonText':'test', 'route':'sfynx_layout_enabledentity_ajax', 'questionTitle':'Titre de mon action', 'questionText':'Etes-vous sûr de vouloir activer toutes les lignes suivantes ?', 'typeResponse':'ajaxResult', 'responseText':'Operation successfully', 'reload':false},
                                            <?php if (!isset($params['sButtonText']) || empty($params['sButtonText']) ) $params['sButtonText'] = '_new_'; ?>
                                            {
                                                "sExtends": "text",
                                                "sButtonText": "<?php echo $this->translator->trans($params['sButtonText']); ?>",
                                                "fnClick": function(nButton, oConfig, nRow){
                                                        var oSelectedRows = this.fnGetSelected();
                                                        var data_id = [];
                                                        var data_HTML = new Array();
                                                        // we register all rows in data
                                                        data_HTML.push( "<br /><br />" );
                                                        for ( var i=0 ; i<oSelectedRows.length ; i++ )
                                                        {
                                                            data_id.push( oSelectedRows[i]['id'] );
                                                            data_HTML.push( oSelectedRows[i] );
                                                            data_HTML.push( "<br />" );
                                                        }
                                                        data_HTML.push( "<br />" );
                                                        // we deselet all selected rows
                                                        this.fnDeselect(oSelectedRows);
                                                        // we empty the content
                                                        $("#grid-html").find("div").empty();
                                                        // Question message are injected into the overlay fancybox
                                                        <?php if (isset($params['questionText']) && !empty($params['questionText'])) : ?>
                                                        $("#grid-html").find("div").prepend("<span class='rows_text_question'><?php echo $params['questionText']; ?></span>");
                                                        <?php endif; ?>
                                                        // all select rows are injected into the overlay fancybox
                                                        $("#grid-html").find("div").append(data_HTML);
                                                        // all select rows are injected into the overlay fancybox
                                                        <?php if (isset($params['questionTitle']) && !empty($params['questionTitle'])) : ?>
                                                        $("#grid-header").html("<?php echo $params['questionTitle']; ?>");
                                                        <?php endif; ?>
                                                        // we run fancybox
                                                        $.fancybox({
                                                            'wrapCSS': 'fancybox-sfynx',
                                                            'content':$("#confirm-popup-grid").html(),
                                                            'autoDimensions':true,
                                                            'scrolling':'no',
                                                            'transitionIn'    :    'elastic',
                                                            'transitionOut'    :    'elastic',
                                                            'speedIn'        :    600,
                                                            'speedOut'        :    200,
                                                            'overlayShow'    :    true,
                                                            'height': 'auto',
                                                            'padding':0,
                                                            'type': 'inline',
                                                            'onComplete'        : function() {
                                                             },
                                                            'onClosed'        : function() {
                                                            }
                                                        });
                                                        // we set the action button
                                                          <?php if (isset($params['route']) && !empty($params['route'])) : ?>
                                                        $("button.save").click(function(event, dataObject) {
                                                            event.preventDefault();
                                                            $.ajax( {
                                                               "url": "<?php echo $this->container->get('router')->generate($params['route']) ?>?_token=<?php echo $csrfToken; ?>",
                                                               "data": { "data": data_id },
                                                               "dataType": "json",
                                                               "type": "post",
                                                               "beforeSend": function ( xhr ) {
                                                                   //xhr.overrideMimeType("text/plain; charset=x-user-defined");
                                                                      $('.dataTables_processing').css({'visibility':'visible'});
                                                               },
                                                               "statusCode": {
                                                                   404: function() {
                                                                   }
                                                               }
                                                           }).done(function ( data ) {
                                                               <?php if (isset($params['typeResponse']) && ($params['typeResponse']) == "ajaxResult") : ?>
                                                                  $("#grid-html > div").html(data);
                                                                  <?php else: ?>
                                                                   <?php if (isset($params['responseText']) && !empty($params['responseText'])) : ?>
                                                                   $("#grid-html > div").html("<?php echo $params['responseText']; ?>");
                                                                     <?php endif; ?>
                                                                  <?php endif; ?>

                                                                  $('.dataTables_processing').css({'visibility':'hidden'});

                                                               <?php if (isset($params['reload']) && ($params['reload']) == true) : ?>
                                                               window.location.reload();
                                                               <?php endif; ?>
                                                           });
                                                        });
                                                       <?php endif; ?>
                                                },
                                                // http://datatables.net/extras/tabletools/button_options
                                                "fninit": function(nButton){
                                                }
                                            },
                                    <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                                        ]
                        },
                        "oColVis": {
                            "buttonText": "&nbsp;",
                            "bRestore": true,
                            "sAlign": "right"
                        },
                        "aoColumnDefs": [
                <?php if (isset($options['grid-visible']) && !empty($options['grid-visible']) && \is_array($options['grid-visible'])): ?>
                    <?php foreach($options['grid-visible'] as $idColumn => $boolean): ?>
                            { "bVisible": <?php echo ((int) $boolean || ($boolean === 'true')) ? "true" : "false"; ?>, "aTargets": [ <?php echo $idColumn; ?> ] },
                    <?php endforeach; ?>
                <?php else: ?>
                            { "bVisible": false, "aTargets": [ 0 ] },
                    <?php if (isset($options['grid-actions']) && !empty($options['grid-actions']) && \is_array($options['grid-actions'])): ?>
                        <?php foreach($options['grid-actions'] as $actionName => $params): ?>
                            <?php if ( ($actionName == "rows_position") && isset($params['route']) && !empty($params['route']) ) : ?>
                                { "bVisible": false, "aTargets": [ 1 ] },
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php endif; ?>

                        ],
                        "oColumnFilterWidgets": {
                            "sSeparator": "\\s*/+\\s*",
                            "aiExclude": [
                <?php if (isset($options['grid-filters-select']) && !empty($options['grid-filters-select']) && \is_array($options['grid-filters-select'])): ?>
                    <?php foreach($options['grid-filters-select'] as $idColumn => $boolean): ?>
                            <?php echo $boolean; ?>,
                    <?php endforeach; ?>
                <?php else: ?>
                            0,1
                <?php endif; ?>
                            ]
                        },

                    });

                <?php if (isset($options['grid-actions']) && !empty($options['grid-actions']) && \is_array($options['grid-actions'])): ?>
                    <?php foreach($options['grid-actions'] as $actionName => $params): ?>

                        <?php if ( ($actionName == "rows_position") && isset($params['route']) && !empty($params['route']) ) : ?>
                            <?php echo $options['grid-name']; ?>oTable.rowReordering({
                                  sURL:"<?php echo $this->container->get('router')->generate($params['route']) ?>?_token=<?php echo $csrfToken; ?>",
                                  sRequestType: "GET",
                                  <?php if (isset($options['grid-actions']['rows_grouping'])) : ?>
                                  bGroupingUsed: true,
                                  <?php endif; ?>
                                  fnAlert: function(message) {
                                  }
                            });
                        <?php endif; ?>

                        <?php if ( $actionName == "rows_grouping" ) : ?>
                            <?php echo $options['grid-name']; ?>oTable.rowGrouping({
                                <?php if ( isset($params['columnIndex']) && is_integer($params['columnIndex'])) : ?>
                                iGroupingColumnIndex: <?php echo $params['columnIndex']; ?>,
                                <?php endif; ?>
                                <?php if ( isset($params['SortDirection']) && in_array($params['SortDirection'], array('asc', 'desc') )) : ?>
                                sGroupingColumnSortDirection: "<?php echo $params['SortDirection']; ?>",
                                <?php endif; ?>
                                <?php if ( isset($params['OrderByColumnIndex']) && is_integer($params['OrderByColumnIndex'])) : ?>
                                iGroupingOrderByColumnIndex: <?php echo $params['OrderByColumnIndex']; ?>,
                                <?php endif; ?>
                                <?php if ( isset($params['HideColumn']) && in_array($params['HideColumn'], array('true', 'false', true, false) )) : ?>
                                bHideGroupingColumn: <?php echo ((int) $params['HideColumn'] || ($params['HideColumn'] === 'true')) ? "true" : "false"; ?>,
                                <?php endif; ?>
                                <?php if ( isset($params['HideOrderByColumn']) && in_array($params['HideOrderByColumn'], array('true', 'false', true, false) )) : ?>
                                bHideGroupingColumn: <?php echo ((int) $params['HideOrderByColumn'] || ($params['HideOrderByColumn'] === 'true')) ? "true" : "false"; ?>,
                                <?php endif; ?>
                                <?php if ( isset($params['GroupBy']) && in_array($params['GroupBy'], array('name', 'year', 'letter', 'month') )) : ?>
                                sGroupBy: "<?php echo $params['GroupBy']; ?>",
                                <?php endif; ?>


                                <?php if ( isset($params['columnIndex2']) && is_integer($params['columnIndex2'])) : ?>
                                iGroupingColumnIndex2: <?php echo $params['columnIndex2']; ?>,
                                <?php endif; ?>
                                <?php if ( isset($params['SortDirection2']) && in_array($params['SortDirection2'], array('asc', 'desc') )) : ?>
                                sGroupingColumnSortDirection2: "<?php echo $params['SortDirection2']; ?>",
                                <?php endif; ?>
                                <?php if ( isset($params['OrderByColumnIndex2']) && is_integer($params['OrderByColumnIndex2'])) : ?>
                                iGroupingOrderByColumnIndex2: <?php echo $params['OrderByColumnIndex2']; ?>,
                                <?php endif; ?>
                                <?php if ( isset($params['HideColumn2']) && in_array($params['HideColumn2'], array('true', 'false', true, false) )) : ?>
                                bHideGroupingColumn2: <?php echo ((int) $params['HideColumn2'] || ($params['HideColumn2'] === 'true')) ? "true" : "false"; ?>,
                                <?php endif; ?>
                                <?php if ( isset($params['HideOrderByColumn2']) && in_array($params['HideOrderByColumn2'], array('true', 'false', true, false) )) : ?>
                                bHideGroupingColumn2: <?php echo ((int) $params['HideOrderByColumn2'] || ($params['HideOrderByColumn2'] === 'true')) ? "true" : "false"; ?>,
                                <?php endif; ?>
                                <?php if ( isset($params['GroupBy2']) && in_array($params['GroupBy2'], array('name', 'year', 'letter', 'month') )) : ?>
                                sGroupBy2:"<?php echo $params['GroupBy2']; ?>",
                                <?php endif; ?>


                                <?php if ( isset($params['DateFormat']) ) : ?>
                                sDateFormat: "<?php echo $params['DateFormat']; ?>",
                                <?php else: ?>
                                sDateFormat: "yyyy-MM-dd",
                                <?php endif; ?>

                                bExpandableGrouping: true,
                                bExpandableGrouping2: true,

                                oHideEffect: { method: "hide", duration: "fast", easing: "linear" },
                                oShowEffect: { method: "show", duration: "slow", easing: "linear" },

                                <?php if ( isset($params['Collapsible']) && ($params['Collapsible'] === 'true') ) : ?>
                                asExpandedGroups: [],
                                <?php endif; ?>
                            });
                    <?php endif; ?>


                    <?php endforeach; ?>
                <?php endif; ?>

                    /**
                     * GLOBAL SEARCH CONTENT
                     */
                    var content = $("#<?php echo $options['grid-name']; ?>_blockglobalsearch_content").html();
                    $("#<?php echo $options['grid-name']; ?>_filter").html(content);
                    $(document).on('keyup', "input#<?php echo $options['grid-name']; ?>_globale_filter", function(){
                        <?php echo $options['grid-name']; ?>oTable.fnFilter($(this).val());
                    });
                    $('#<?php echo $options['grid-name']; ?>_search_button').click(function() {
                        $('.search-label').toggleClass('changed');
                        var toggleWidth = $('.dataTables_filter [type=search]').width() == 221 ? "0px" : "221px";
                        $('.dataTables_filter [type=search]').animate({width: toggleWidth});
                    });


                    /**
                     * SEARCH CONTENT
                     */
                    var content = $("#<?php echo $options['grid-name']; ?>_blocksearch_content").html();
                    $("#<?php echo $options['grid-name']; ?>_blocksearch_content").html('');
                    $("#<?php echo $options['grid-name']; ?>").before(content);

                    $("#<?php echo $options['grid-name']; ?>_global_filter").keyup( fnFilterGlobal );
                    $("#<?php echo $options['grid-name']; ?>_global_regex").click( fnFilterGlobal );
                    $("#<?php echo $options['grid-name']; ?>_global_smart").click( fnFilterGlobal );

                    <?php if (isset($options['grid-filters']) && !empty($options['grid-filters']) && \is_array($options['grid-filters'])) { ?>
                        <?php foreach ($options['grid-filters'] as $id => $colName) { ?>

                    $("#<?php echo $options['grid-name']; ?>_col<?php echo $id; ?>_filter").keyup( function() { fnFilterColumn( <?php echo $id-1; ?> ); } );
                    $("#<?php echo $options['grid-name']; ?>_col<?php echo $id; ?>_regex").click(  function() { fnFilterColumn( <?php echo $id-1; ?> ); } );
                    $("#<?php echo $options['grid-name']; ?>_col<?php echo $id; ?>_smart").click(  function() { fnFilterColumn( <?php echo $id-1; ?> ); } );

                        <?php } ?>
                    <?php } ?>

                    $('.block_filter').click(function() {
                        $("#blocksearch").slideToggle("slow");
                    });

                    $('select', '#<?php echo $options['grid-name']; ?>').each(function(){
                        var options = $('option',this);

                        if(options.length === 2){
                          var option = $(options[1]);
                          option.attr('selected','true');
                        }
                    });

                    <?php if (isset($options['grid-actions']['rows_position'])) : ?>
                    $(".ui-state-default div.DataTables_sort_wrapper .ui-icon").css('display', 'none');
                    <?php endif; ?>

                    // http://fgnass.github.io/spin.js/
                    var opts_spinner = {
                            lines: 11, // The number of lines to draw
                            length: 2, // The length of each line
                            width: 3, // The line thickness
                            radius: 6, // The radius of the inner circle
                            corners: 1, // Corner roundness (0..1)
                            rotate: 0, // The rotation offset
                            direction: 1, // 1: clockwise, -1: counterclockwise
                            color: '#000', // #rgb or #rrggbb
                            speed: 1.3, // Rounds per second
                            trail: 54, // Afterglow percentage
                            shadow: false, // Whether to render a shadow
                            hwaccel: true, // Whether to use hardware acceleration
                            className: 'spinner', // The CSS class to assign to the spinner
                            zIndex: 1049, // The z-index (defaults to 2000000000)
                            top: 0, // Top position relative to parent in px
                            left: 0 // Left position relative to parent in px
                          };
                   var target_spinner = document.getElementById('spin');
                   var spinner = new Spinner(opts_spinner).spin(target_spinner);

                   $(function() {
                        $("a.info-tooltip").tooltip({
                              position: {
                                  track: true,
                                  my: "center bottom-20",
                                  at: "center top",
                                },
                              content: function () {
                                    return $(this).prop('title');
                                }
                        });
                        fnCreateFooterFilter();
                        <?php if(!isset($options['grid-server-side']) || ($options['grid-server-side'] === 'false') || ($options['grid-server-side'] === false) ) : ?>
                        fnCreateFooterFilter();
                        <?php endif; ?>
                   });
        <?php
        // We retrieve the contents of the buffer.
        $_content_js = ob_get_contents ();
        // We clean the buffer.
        ob_clean ();
        // We close the buffer.
        ob_end_flush ();
        // We open the buffer.
        ob_start ();
        ?>
            <div id="confirm-popup-grid" style="display:none">
                <div class="fancybox-grid">
                    <section id="grid-html">
                        <header class="tt-grey">
                            <h3 id="grid-header">MESSAGE</h3>
                        </header>
                        <div>&nbsp;</div>
                        <footer class="tt-grey">
                            <button type="button" id="grid-save" class="save"><?php echo $this->translator->trans('pi.grid.action.validate'); ?></button>
                        </footer>
                    </section>
                </div>
            </div>

            <div id="<?php echo $options['grid-name']; ?>_blockglobalsearch_content">
                <div class="dataTable_filter_inner_wrapper" id="filter_inner_wrapper_<?php echo $options['grid-name']; ?>">
                    <div class="dataTables_filter search-wrapper">
                        <label class="search-label">
                            <input type="search" id="<?php echo $options['grid-name']; ?>_globale_filter" placeholder="Recherche rapide" aria-controls="alert_<?php echo $options['grid-name']; ?>">
                        </label>
                        <button class="search-button" id="<?php echo $options['grid-name']; ?>_search_button">
                            <span class="icon icon-cross"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div id="<?php echo $options['grid-name']; ?>_blocksearch_content">
                <div id="blocksearch" style="display:none" >
                    <table class="filter">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Filtre</th>
                            <th>Expression régulière</th>
                            <th>Use smart filter</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr id="filter_global">
                                <td>Global filtering</td>
                                <td><input type="text"     name="<?php echo $options['grid-name']; ?>_global_filter" id="global_filter"></td>
                                <td><input type="checkbox" name="<?php echo $options['grid-name']; ?>_global_regex"  id="global_regex" ></td>
                                <td><input type="checkbox" name="<?php echo $options['grid-name']; ?>_global_smart"  id="global_smart"  checked></td>
                            </tr>
                            <?php if (isset($options['grid-filters']) && !empty($options['grid-filters']) && \is_array($options['grid-filters'])) { ?>
                                <?php foreach ($options['grid-filters'] as $id => $colName) { ?>
                                    <tr id="filter_col<?php echo $id; ?>">
                                        <td><?php echo $colName; ?></td>
                                        <td><input type="text"     name="<?php echo $options['grid-name']; ?>_col<?php echo $id; ?>_filter" id="col<?php echo $id; ?>_filter"></td>
                                        <td><input type="checkbox" name="<?php echo $options['grid-name']; ?>_col<?php echo $id; ?>_regex"  id="col<?php echo $id; ?>_regex"></td>
                                        <td><input type="checkbox" name="<?php echo $options['grid-name']; ?>_col<?php echo $id; ?>_smart"  id="col<?php echo $id; ?>_smart" checked></td>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php
        // We retrieve the contents of the buffer.
        $_content_html = ob_get_contents ();
        // We clean the buffer.
        ob_clean ();
        // We close the buffer.
        ob_end_flush ();

        return  $this->renderScript($_content_js, $_content_html, 'core/gridtable/');
    }

    /**
     * Sets the grid server render.
     *
     * @param    $options    tableau d'options.
     * @access private
     * @return string
     */
    protected function gridBootstrap($options = null)
    {
        // We open the buffer.
        ob_start ();
        ?>
            <script type="text/javascript">
            //<![CDATA[
            <?php echo $options['grid-name']; ?>oTable = $('#<?php echo $options['grid-name']; ?>').dataTable({
                "aLengthMenu": [
                    [50, 100, -1],
                    [50, 100, "Tous"] // change per page values here
                ],
                'bAutoWidth': false,
                // set the initial value
                "iDisplayLength": 50,
                "sDom": "<'row'<'col-lg-6'l><'col-lg-6'f>r>t<'row'<'col-lg-6'i><'col-lg-6'p>>",
                "sPaginationType": "bootstrap",
                "oLanguage": {
                    "sProcessing":     "Traitement en cours...",
                    "sSearch":         "Rechercher&nbsp;:",
                    "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
                    "sInfo":           "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                    "sInfoEmpty":      "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
                    "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                    "sInfoPostFix":    "",
                    "sLoadingRecords": "Chargement en cours...",
                    "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                    "sEmptyTable":     "Aucune donnée disponible dans le tableau",
                    "oPaginate": {
                        "sFirst":      "Premier",
                        "sPrevious":   "Pr&eacute;c&eacute;dent",
                        "sNext":       "Suivant",
                        "sLast":       "Dernier"
                    },
                    "oAria": {
                        "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                        "sSortDescending": ": activer pour trier la colonne par ordre décroissant"
                    }
                },
                "aoColumnDefs": [
                    { "bSortable": false , "sWidth": "90px", "aTargets" : [ 4 ] }
                ]
            });

            //]]>
            </script>
        <?php
        // We retrieve the contents of the buffer.
        $_content = ob_get_contents ();
        // We clean the buffer.
        ob_clean ();
        // We close the buffer.
        ob_end_flush ();

        return $_content;
    }
}
