<?php
/**
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/odwp-sendlane_plugin for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-sendlane_plugin
 */

if ( ! class_exists( 'Actions_List' ) ) :

// Ensure the `WP_List_Table` is loaded
if ( ! class_exists( 'WP_List_Table' ) ) :
    require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
endif;

/**
 * Administration listing for actions.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @since 1.0
 */
class Actions_List extends WP_List_Table {
    /**
     * @const string
     */
    const ACTION_NONE   = '-1';
    const ACTION_DELETE = 'delete';
    const ACTION_FIND   = 'find';
    /**
     * @const string
     */
    const FILTER_BY_STATUS = 's';
    const FILTER_BY_TYPE   = 't';
    /**
     * @const string
     */
    const VIEW_ALL       = 'all';
    const VIEW_FOUND     = 'found';
    const VIEW_NOT_FOUND = 'not_found';

    /**
     * @const string
     */
    const TYPE_NONE = '-1';

    /**
     * @const integer
     */
    const DEFAULT_PERPAGE = 7;

    /**
     * Unique identifier of the page where the list is used.
     * @var string $page
     */
    protected $page;

    /**
     * Unique identifier of the plugin.
     * @var string $plugin_slug
     */
    protected $plugin_slug;

    /**
     * Filter.
     * @var array $data_filter
     */
    protected $data_filter;

    /**
     * `TRUE` if filter is used.
     * @var boolean $is_filter_in_use
     */
    protected $is_filter_in_use;

    /**
     * Constructor.
     * @param string $page
     * @param string $plugin_slug
     * @return void
     */
    function __construct($page, $plugin_slug) {
        $this->page = $page;
        $this->plugin_slug = $plugin_slug;
        $this->data_filter = [
            self::FILTER_BY_STATUS => self::VIEW_ALL,
            self::FILTER_BY_TYPE   => self::TYPE_NONE,
        ];
        $this->is_filter_in_use = false;

        parent::__construct( [
            'singular'=> __('Kod', $this->plugin_slug),
            'plural' => __('Kody', $this->plugin_slug),
            'ajax'   => false
        ] );
    } // end __construct($page,$plugin_slug)

  /**
   * Returns the array of columns to use with the table.
   *
   * @return array $columns
   * @since 0.1.0
   */
  public function get_columns() {
    $columns = array(
      'id'      => __('<input type="checkbox" id="bulk-all">', $this->plugin_slug),
      'code'    => __('Kód svíčky', $this->plugin_slug),
      'type'    => __('Typ svíčky', $this->plugin_slug),
      'created' => __('Vytvořeno', $this->plugin_slug),
      'found'   => __('Nalezeno', $this->plugin_slug)
    );
    return $columns;
  } // end get_columns()

  /**
   * Returns the array of hidden columns.
   *
   * @return array $columns
   * @since 0.1.0
   */
  public function get_hidden_columns() {
    $hidden = array();
    return $hidden;
  } // end get_hidden_columns()

  /**
   * Returns the array of columns that can be sorted by the user.
   *
   * @return array $sortable
   * @since 0.1.0
   */
  protected function get_sortable_columns() {
    $sortable = array(
      //'id' => array('id', false),
      'code' => array('code', false),
      'type' => array('type', false),
      'created' => array('created', false),
      'found' => array('found', false)
    );
    return $sortable;
  } // end get_sortable_columns()

  /**
   * Returns array with implemented bulk actions.
   *
   * @return array
   * @since 0.9.1
   */
  public function get_bulk_actions() {
    return array(
      self::ACTION_DELETE => __('Odstranit', $this->plugin_slug),
      self::ACTION_FIND   => __('Najít', $this->plugin_slug)
    );
  } // end get_bulk_actions()

  /**
   * Returns array with the list of views available on this table.
   *
   * @return array
   * @since 0.9.1
   * @uses ODWP_DiamantoveSvickyKody::get_code_stats()
   *
   * @todo Add total counts!
   */
  protected function get_views() {
    return array(
      self::VIEW_ALL       => __('Všechny', $this->plugin_slug),
      self::VIEW_FOUND     => __('Nalezené', $this->plugin_slug),
      self::VIEW_NOT_FOUND => __('Nenalezené', $this->plugin_slug)
    );
  } // end get_views()

  /**
   * Returns filter.
   *
   * @param string $key (Optional.) If not defined ALL filters as an array are returned.
   * @return array|string
   * @since 0.9.1
   */
  public function get_filter($key = NULL) {
    if (
      !is_null($key) &&
      array_key_exists($key, $this->data_filter) &&
      in_array($key, array(self::FILTER_BY_STATUS, self::FILTER_BY_TYPE))
    ) {
      return $this->data_filter[$key];
    }

    return $this->data_filter;
  } // end get_filter()

  /**
   * Sets filter (this must be done BEFORE `prepare_items` method).
   *
   * @param array $filter
   * @return void
   * @since 0.9.1
   */
  public function set_filter(array $filter) {
    foreach ($filter as $key => $val) {
      if (!in_array($key, array(self::FILTER_BY_STATUS, self::FILTER_BY_TYPE))) {
        continue;
      }

      if ($this->set_filter_validate($key, $val) === true) {
        $this->data_filter[$key] = $val;
      }
    }
  } // end set_filter(array $filter)

  /**
   * @private
   * @param string $key
   * @param string $val
   * @return boolean Returns `TRUE` if `$val` is correct value for the `$key`. Otherwise returns `FALSE`.
   * @since 0.9.1
   */
  private function set_filter_validate($key, $val) {
    if (
      $key == self::FILTER_BY_STATUS
      && in_array($val, array(self::VIEW_ALL, self::VIEW_FOUND, self::VIEW_NOT_FOUND))
    ) {
      return true;
    }

    if (
      $key == self::FILTER_BY_TYPE
      && (
        $val == self::TYPE_NONE ||
        in_array($val, array_keys(ODWP_DiamantoveSvickyKody::get_types()))
      )
    ) {
      return true;
    }

    return false;
  } // end set_filter_validate($key, $val)

  /**
   * @return string Returns URL to the page where is the list used.
   * @since 0.9.1
   * @uses admin_url()
   */
  public function get_url() {
    return admin_url('admin.php') . '?page='.$this->page;
  } // end get_url()

  /**
   * @param string $url_part (Optional.)
   * @return string Returns URL to the page where is the list used with filter.
   * @since 0.9.1
   * @uses admin_url()
   */
  public function get_full_url() {
    $url = $this->get_url();

    $status = $this->get_filter(self::FILTER_BY_STATUS);
    if ($status != self::VIEW_ALL) {
      $url .= '&s='.$status;
    }

    $type = $this->get_filter(self::FILTER_BY_TYPE);
    if ($type != self::TYPE_NONE) {
      $url .= '&t='.$type;
    }

    return $url;
  } // end get_full_url()

  /**
   * Display the list of views available on this table.
   *
   * @return void
   * @since 0.9.1
   *
   * @todo Default method `WP_List_Table::views()` doesn't work for me but it should. Try it again!
   */
  public function views() {
    if ($this->has_items() !== true) {
      // If there are no data don't render extra navigation.
      return;
    }

    $stats = ODWP_DiamantoveSvickyKody::get_code_stats();
    $views = $this->get_views();
    $url   = $this->get_url() . '&' . self::FILTER_BY_STATUS . '=';
    $_view  = $this->get_filter(self::FILTER_BY_STATUS);
    // XXX $this->screen->render_screen_reader_content('heading_views');
    $i     = 1;
?>
<ul class="subsubsub <?= $_view?>">
  <?php foreach ($views as $class => $view):
    $cls = $class.(($view == $class) ? ' current' : '');
  ?>
  <!-- "<?= $view?>" | "<?= $class?>" | "<?= ($view == $class) ? 'TRUE' : 'FALSE'?>" -->
  <li class="<?= esc_attr($cls)?>">
    <?php if ($_view == $class):?>
    <strong><?= $view?></strong>
    <?php else:?>
    <a href="<?= esc_attr($url.$class)?>"><?= $view?></a>
    <?php endif?>
    <span class="count">(<?= $stats[$class]?>)</span>
    <?php if ($i < 3): $i++;?>
    |
    <?php else: $i = 1; endif;?>
  </li>
  <?php endforeach?>
</ul>
<?php
  } // end views()

  /**
   * Add extra markup in the toolbars before or after the list.
   *
   * @param string $which
   * @return void
   * @since 0.1.0
   *
   * @todo Select correct <option>!!!
   */
  function extra_tablenav($which) {
    if ($this->has_items() !== true) {
      // If there are no data don't render extra navigation.
      return;
    }

    $suffix  = ($which == 'bottom') ? '2' : '';
    $types   = ODWP_DiamantoveSvickyKody::get_types();
    $current = $this->get_filter('t');
?>
<div class="alignleft actions filteractions">
  <label class="screen-reader-text" for="filter-by-type<?= $suffix?>"></label>
  <select id="filter-by-type<?= $suffix?>" name="t<?= $suffix?>">
    <option value="<?= self::TYPE_NONE?>"<?= ($current == self::TYPE_NONE) ? ' selected="selected"' : ''?>><?= __('— Typ —', $this->plugin_slug)?></option>
    <?php foreach ($types as $type => $type_name):?>
    <option value="<?= $type?>"<?= ($current == $type) ? ' selected="selected"' : ''?>><?= $type_name?></option>
    <?php endforeach?>
  </select>
  <input type="submit" class="button action" name="filter<?= $suffix?>" value="<?= __('Filtrovat', $this->plugin_slug)?>">
</div>
<?php
  } // end extra_tablenav($which)

  /**
   * Used to display the value of the `id` column.
   *
   * @param object $item
   * @return string
   * @since 0.1.0
   */
  public function column_id($item) {
?>

<?php
    return sprintf(
      '<div style="padding-left: 10px;">'.
        '<input type="checkbox" name="bulk-code_id[]" value="%s">'.
      '</div>',
      $item->id
    );
  } // end column_id($item)

  /**
   * Used to display the value of the `code` column.
   *
   * @param object $item
   * @return string
   * @since 0.1.0
   * @uses wp_create_nonce()
   * @uses absint()
   * @uses esc_attr()
   */
  function column_code($item) {
    $url   = $this->get_full_url().'&action=%s&code_id=%d&_wpnonce=%s';
    $nonce = wp_create_nonce('sp_delete_code');
    $url_d = sprintf($url, self::ACTION_DELETE, absint($item->id), $nonce);

    $actions = array(
      self::ACTION_DELETE => sprintf(
          '<a href="%s">%s</a>',
          esc_attr($url_d),
          __('Smazat', $this->plugin_slug)
      )
    );

    if (empty($item->found)) {
      $url_f = sprintf($url, self::ACTION_FIND, absint($item->id), $nonce);
      $actions[self::ACTION_FIND] = sprintf(
          '<a href="%s" title="%s">%s</a>',
          esc_attr($url_f),
          __('Nastaví vybraný kód jako nalezený.', $this->plugin_slug),
          __('<em>Najít</em>', $this->plugin_slug)
      );
    }

    return '<strong>' . $item->code . '</strong>' . $this->row_actions($actions);
  } // end column_code($item)

  /**
   * Used to display the value of the `type` column.
   *
   * @param object $item
   * @return string
   * @since 0.1.0
   */
  public function column_type($item) {
    return ODWP_DiamantoveSvickyKody::get_type_name($item->type);
  } // end column_id($item)

  /**
   * Used to display the value of the `created` column.
   *
   * @param object $item
   * @return string
   * @since 0.1.0
   */
  public function column_created($item) {
    return date('j.n.Y H:i', strtotime($item->created));
  } // end column_created($item)

  /**
   * Used to display the value of the `created` column.
   *
   * @param object $item
   * @return string
   * @since 0.1.0
   */
  public function column_found($item) {
    if (empty($item->found)) {
      return '<em>&ndash;&ndash;&ndash;</em>';
    } else {
      return date('j.n.Y H:i', strtotime($item->found));
    }
  } // end column_found($item)

  /**
   * Prepare the table with different parameters, pagination, columns and table elements.
   *
   * @global wpdb $wpdb
   * @global array $_wp_column_headers
   * @return void
   * @since 0.1.0
   * @uses get_current_screen()
   * @uses esc_sql()
   */
  function prepare_items() {
    global $wpdb, $_wp_column_headers;
    $screen = get_current_screen();

    // Prepare SQL query
    $table_name = $wpdb->prefix . ODWP_DiamantoveSvickyKody::TABLE_NAME;
    $query = 'SELECT * FROM `'.$table_name.'` WHERE 1 ';

    // Filter items by `found` status
    $f_status = $this->get_filter(self::FILTER_BY_STATUS);
    switch ($f_status) {
      case self::VIEW_FOUND:
      case self::VIEW_NOT_FOUND:
        $this->is_filter_in_use = true;
        $query .= 'AND `found` IS '.
            (($f_status == self::VIEW_FOUND) ? 'NOT ' : '').
            'NULL ';
        break;

      case self::VIEW_ALL:
      default:
        break;
    }

    // Filter items by `type`
    $f_type   = $this->get_filter(self::FILTER_BY_TYPE);
    if (!empty($f_type) && $f_type != self::TYPE_NONE) {
      $this->is_filter_in_use = true;
      $query .= 'AND `type` = "'.esc_sql($f_type).'" ';
    }

    // Ordering parameters
    $ob = filter_input(INPUT_GET, 'orderby', FILTER_SANITIZE_STRING);
    $o = filter_input(INPUT_GET, 'order', FILTER_SANITIZE_STRING);
    $orderby = !empty($ob) ? esc_sql($ob) : '';
    $order = !empty($o) ? esc_sql($o) : 'asc';

    if (
      in_array($ob, array('id', 'code', 'type', 'created', 'found')) &&
      in_array($o, array('asc', 'desc'))
    ) {
      $query.='ORDER BY '.$orderby.' '.$order;
    }

    // Number of elements in your table?
    $totalitems = $wpdb->query($query); //return the total number of affected rows
    // How many to display per page?
    //$perpage = self::DEFAULT_PERPAGE;
    $perpage = $this->get_items_per_page('candle_codes_per_page', self::DEFAULT_PERPAGE);
    // Which page is this?
    $paged = (int) filter_input(INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT);
    // Page Number
    if (empty($paged) || $paged <= 0 ) {
      $paged = 1;
    }
    // How many pages do we have in total?
    $totalpages = ceil($totalitems / $perpage);
    // adjust the query to take pagination into account
    if (!empty($paged) && !empty($perpage)) {
      $offset = ($paged - 1) * $perpage;
      $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
    }

    // Register the pagination
    $this->set_pagination_args(array(
      'total_items' => $totalitems,
      'total_pages' => $totalpages,
      'per_page'    => $perpage,
      'filter'      => $this->data_filter
    ));

    // Register the columns
    $columns = $this->get_columns();
    $hidden = $this->get_hidden_columns();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    $_wp_column_headers[$screen->id] = $columns; // TODO What exactly is `_wp_column_headers`!

    // Fetch the items
    $this->items = $wpdb->get_results($query);
  } // end prepare_items()

  /**
   * Prints message "no items".
   *
   * @return void
   * @since 0.9.1
   */
  public function no_items() {
    if ($this->is_filter_in_use === true):
?><p><?= sprintf(
  __('Danému filtru neodpovídají žádné kódy - zkuste načíst stránku znovu <a href="%s">bez filtru</a>.', $this->plugin_slug),
  $this->get_url()
)?></p>
<?php
    else:
?><p><?= sprintf(
  __('Databáze kódů svíček je prázdná! Začněte tím, že nějaké kódy <a href="%s">vygenerujete</a>&hellip;.', $this->plugin_slug),
  $this->plugin_slug . '-generate_page'
)?></p>
<?php
    endif;
  } // end no_items()
}

endif;