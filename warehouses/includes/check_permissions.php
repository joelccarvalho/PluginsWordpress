<?php

function check_user(){
    global $wpdb;
    $current_user = wp_get_current_user();
    $table = GLOBAL_PREFIX.'warehouses_permissions';

    $sql = "SELECT * FROM $table WHERE user_id = $current_user->ID";

    $sql_result = $wpdb->get_results($sql, OBJECT);

    return $sql_result;
}

function check_user_warehouse($warehouse_id){
    global $wpdb;
    $current_user = wp_get_current_user();
    $table = GLOBAL_PREFIX.'warehouses_permissions';

    $sql = "SELECT * FROM $table WHERE user_id = $current_user->ID AND warehouse_id = $warehouse_id";

    $sql_result = $wpdb->get_results($sql, OBJECT);

    if (!$sql_result[0]->permission) {
        wp_die( __('Não tem permissões para aceder a esta página. Contacte o administrador.') );
    }
}

function check_user_warehouse_bool($warehouse_id){
    global $wpdb;
    $current_user = wp_get_current_user();
    $table = GLOBAL_PREFIX.'warehouses_permissions';

    $sql = "SELECT * FROM $table WHERE user_id = $current_user->ID AND warehouse_id = $warehouse_id";

    $sql_result = $wpdb->get_results($sql, OBJECT);

    if ($sql_result[0]->permission) {
        return true;
    }
    else if ($current_user->roles[0] == 'administrator') {
        return true;
    }
    else
    {
        return false;
    }
}

function check_user_admin(){
    global $wpdb;
    $current_user = wp_get_current_user();

    if ($current_user->roles[0] != 'administrator') {
        wp_die( __('Não tem permissões para aceder a esta página.') );
    }
}

function check_user_admin_bool(){
    global $wpdb;
    $current_user = wp_get_current_user();

    if ($current_user->roles[0] == 'administrator') {
        return true;
    }
    else
    {
        return false;
    }
}

