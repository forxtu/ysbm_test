/*
* Создаём кастомный тип записи
*/

add_action('init', 'my_custom_init');
function my_custom_init(){
	register_post_type('film', array(
		'labels'             => array(
			'name'               => 'Фильмы', // Основное название типа записи
			'singular_name'      => 'Фильм', // отдельное название записи типа
			'add_new'            => 'Добавить новый',
			'add_new_item'       => 'Добавить новый фильм',
			'edit_item'          => 'Редактировать фильм',
			'new_item'           => 'Новый фильм',
			'view_item'          => 'Посмотреть фильм',
			'search_items'       => 'Найти фильм',
			'not_found'          =>  'Фильмов не найдено',
			'not_found_in_trash' => 'В корзине фильмов не найдено',
			'parent_item_colon'  => '',
			'menu_name'          => 'Фильмы',
			'menu_icon'          => 'dashicons-video-alt'

		  ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => true,
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array('title','editor','author','thumbnail','excerpt','comments')
	) );
}


// Сообщения при публикации или изменении записи
add_filter('post_updated_messages', 'film_updated_messages');
function film_updated_messages( $messages ) {
	global $post;

	$messages['film'] = array(
		0 => '', 
		1 => sprintf( 'film обновлено. <a href="%s">Посмотреть запись film</a>', esc_url( get_permalink($post->ID) ) ),
		2 => 'Произвольное поле обновлено.',
		3 => 'Произвольное поле удалено.',
		4 => 'Запись film обновлена.',
		
		5 => isset($_GET['revision']) ? sprintf( 'Запись film восстановлена из ревизии %s', wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( 'Запись film опубликована. <a href="%s">Перейти к записи film</a>', esc_url( get_permalink($post->ID) ) ),
		7 => 'Запись film сохранена.',
		8 => sprintf( 'Запись film сохранена. <a target="_blank" href="%s">Предпросмотр записи film</a>', esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
		9 => sprintf( 'Запись film запланирована на: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Предпросмотр записи film</a>',
		  
		  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post->ID) ) ),
		10 => sprintf( 'Черновик записи film обновлен. <a target="_blank" href="%s">Предпросмотр записи film</a>', esc_url( add_query_arg( 'preview', 'true', get_permalink($post->ID) ) ) ),
	);

	return $messages;
}



/*
 * Добавление таксономий
 */

function add_custom_taxonomies() {

  //  Добавление таксономии Жанры
  register_taxonomy('taxonomies', 'film', array(
    'hierarchical' => true,
    'labels' => array(
      'name' => _x( 'Жанры', 'taxonomy general name' ),
      'singular_name' => _x( 'жанр', 'taxonomy singular name' ),
      'search_items' =>  __( 'Найти жанр' ),
      'all_items' => __( 'Все жанры' ),
      'parent_item' => __( 'Родительские жанры' ),
      'parent_item_colon' => __( 'Родительский жанр:' ),
      'edit_item' => __( 'Редактировать жанр' ),
      'update_item' => __( 'Обновить жанр' ),
      'add_new_item' => __( 'Добавить новый жанр' ),
      'new_item_name' => __( 'Новое название жанра' ),
      'menu_name' => __( 'Жанры' ),
    ),

      'rewrite' => array(
      'slug' => 'taxonomies',
      'with_front' => false,
      'hierarchical' => true
    ),
  ));

// Добавление таксономии Режиссеры
  register_taxonomy('Режиссер', 'film', array(
    'hierarchical' => true,
    'labels' => array(
      'name' => _x( 'Режиссеры', 'taxonomy general name' ),
      'singular_name' => _x( 'Режиссер', 'taxonomy singular name' ),
      'search_items' =>  __( 'Найти по режиссеру' ),
      'all_items' => __( 'Все режиссеры' ),
      'parent_item' => __( 'Родительский режиссер' ),
      'parent_item_colon' => __( 'Родительский режиссер:' ),
      'edit_item' => __( 'Редактировать режиссер' ),
      'update_item' => __( 'Обновить режиссер' ),
      'add_new_item' => __( 'Добавить нового режиссера' ),
      'new_item_name' => __( 'Новый режиссер' ),
      'menu_name' => __( 'Режиссеры' ),
    ),

    'rewrite' => array(
      'slug' => 'Режиссеры',
      'with_front' => false,
      'hierarchical' => true
    ),
  ));
}
add_action( 'init', 'add_custom_taxonomies', 0 );


/*
* Shortcode [film-list] выводим фильмы на страницу
*/

add_shortcode( 'film-list', 'my_shortcode_init' );
function my_shortcode_init( $atts ) {
    ob_start();
    extract( shortcode_atts( array(
        'type' => 'film',
        'posts' => 3,
        'order' => 'ASC',
        'orderby' => 'title',
    ), $atts ) );

	$options = array(
        'post_type' => $type,
	    'posts_per_page' => $posts,
        'order' => $order,
        'orderby' => $orderby,
    );

    $query = new WP_Query( $options );
    if ( $query->have_posts() ) { ?>

    <div id="primary">
        <div id="content" role="main">
        <?php
            $mypost = array( 'post_type' => 'film', );
            $loop = new WP_Query( $mypost );
        ?>
        <?php while ( $loop->have_posts() ) : $loop->the_post();?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <div>
                    <?php the_post_thumbnail( array( 500, 300 ) ); ?>
                </div>
                <strong>Название: </strong><?php the_title(); ?><br />
                <strong>Жанр: </strong>
                <?php
                	the_terms( $post->ID, 'taxonomies' ,  ' ' );
                ?>
		          <br>
		        <strong>Режиссер: </strong>
                <?php
                	the_terms( $post->ID, 'Режиссер' ,  ' ' );
                ?>
		          <br>
		        <strong>Год: </strong>
                <?php
                	$textInput = get_post_meta($post->ID, 'mytextinput', true);
			        echo $mytextinput;
                ?>
            </header>
            <div ><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
    </div>
</div>
<?php wp_reset_query(); ?>

    <?php $myvariable = ob_get_clean();
    return $myvariable;
    }
}


/*
* Meta box дополнительные поля
*/

function my_meta_box() {
    add_meta_box(
        'my_meta_box', // Идентификатор(id)
        'Дополнительные данные', // Заголовок области с мета-полями(title)
        'show_my_metabox', // Вызов(callback)
        'film', // Где будет отображаться наше поле, в фильмах
        'normal',
        'high');
}
    add_action('add_meta_boxes', 'my_meta_box'); // Запускаем функцию

$meta_fields = array(
    array(
        'label' => 'Год выпуска',
        'desc'  => 'Описание для поля.',
        'id'    => 'mytextinput', // даем идентификатор.
        'type'  => 'text'  // Указываем тип поля.
    ),
 	array(
        'label' => 'Ссылка на youtube',
        'desc'  => 'Описание для поля.',
        'id'    => 'mytextinput_2', // даем идентификатор.
        'type'  => 'text'  // Указываем тип поля.
    ),

);

// Вызов мета полей
function show_my_metabox() {
global $meta_fields; // Обозначим наш массив с полями глобальным
global $post;  // Глобальный $post для получения id создаваемого поста
// Выводим скрытый input, для верификации
echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';

    // Начинаем выводить таблицу с полями через цикл
    echo '<table class="form-table">';
    foreach ($meta_fields as $field) {
        // Получаем значение если оно есть для этого поля
        $meta = get_post_meta($post->ID, $field['id'], true);
        // Начинаем выводить таблицу
        echo '<tr>
                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                <td>';
                switch($field['type']) {
                    case 'text':
                        echo '<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" />
                        <br /><span class="description">'.$field['desc'].'</span>';
                    break;
                    case 'textarea':
                        echo '<textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea>
                            <br /><span class="description">'.$field['desc'].'</span>';
                    break;
                    case 'checkbox':
                        echo '<input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
                            <label for="'.$field['id'].'">'.$field['desc'].'</label>';
                    break;

                    // Всплывающий список
                    case 'select':
                        echo '<select name="'.$field['id'].'" id="'.$field['id'].'">';
                        foreach ($field['options'] as $option) {
                            echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
                        }
                        echo '</select><br /><span class="description">'.$field['desc'].'</span>';
                    break;
                                    }
                            echo '</td></tr>';
                        }
                        echo '</table>';
                    }

// Пишем функцию для сохранения
function save_my_meta_fields($post_id) {
    global $meta_fields;  // Массив с нашими полями

    // проверяем наш проверочный код
    if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__)))
        return $post_id;
    // Проверяем авто-сохранение
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;
    // Проверяем права доступа
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return $post_id;
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
    }

    // Если все отлично, прогоняем массив через foreach
    foreach ($meta_fields as $field) {
        $old = get_post_meta($post_id, $field['id'], true); // Получаем старые данные (если они есть), для сверки
        $new = $_POST[$field['id']];
        if ($new && $new != $old) {  // Если данные новые
            update_post_meta($post_id, $field['id'], $new); // Обновляем данные
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old); // Если данных нету, удаляем мету
        }
    } // end foreach
}
add_action('save_post', 'save_my_meta_fields'); // Запускаем функцию сохранения




/*
* Фильтрация в админке по жанру
*/

add_action( 'restrict_manage_posts', 'my_filter_list' );

function my_filter_list() {
    $screen = get_current_screen();
    global $wp_query;
    if ( $screen->post_type == 'film' ) {
        wp_dropdown_categories( array(
            'show_option_all' => 'Показать все жанры',
            'taxonomy' => 'taxonomies',
            'name' => 'taxonomies',
            'orderby' => 'name',
            'selected' => ( isset( $wp_query->query['taxonomies'] ) ? $wp_query->query['taxonomies'] : '' ),
            'hierarchical' => false,
            'depth' => 3,
            'show_count' => false,
            'hide_empty' => true,
        ) );
    }
}

// Отображение фильтрованных результатов

add_filter( 'parse_query','perform_filtering' );

function perform_filtering( $query ) {
    $qv = &$query->query_vars;
    if ( ( $qv['taxonomies'] ) && is_numeric( $qv['taxonomies'] ) ) {
        $term = get_term_by( 'id', $qv['taxonomies'], 'taxonomies' );
        $qv['taxonomies'] = $term->slug;
    }
}
