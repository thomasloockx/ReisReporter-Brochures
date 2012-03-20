<?php
/**
 * @package Reisreporter
 * @version 0.1
 */
/*
Plugin Name: Reisreporter brochure table
Plugin URI: www.reisreporter.be
Description: Shows a table of brochures with for each brochure a thumb, a post link and a subscribe link.
Author: Thomas Loockx
Version: 0.1
*/

// wordpress hooks
add_action('admin_menu', 'rr_brochures_menu');
register_deactivation_hook(__FILE__, 'rr_brochures_deactivate');

function rr_brochures_nb_brochures() { return 10; }
function rr_brochures_nb_rows() { return 2; }
function rr_brochures_nb_in_row() { return 5; }

function rr_brochures_deactivate()
{
    delete_option('rr_brochures');
}

function rr_brochures_save($post_data)
{
    $brochures = array();

    for ($i = 0; $i < rr_brochures_nb_brochures(); ++$i) {
        $post_url = $post_data["post_url_$i"];
        $img_url  = $post_data["img_url_$i"];
        $ext_url  = $post_data["ext_url_$i"];

        if ($post_url != "" && $img_url != "" && $ext_url != "") {
            $brochures["post_url_$i"] = $post_url;
            $brochures["img_url_$i"]  = $img_url;
            $brochures["ext_url_$i"]  = $ext_url;
        }
    }

    delete_option('rr_brochures');

    if (!update_option('rr_brochures', $brochures))
        wp_die('Error updating rr_brochures option');
}

/* Use this guy in the template. */
function rr_brochures_show()
{
    $cells      = array();
    $in_a_row  = rr_brochures_nb_in_row();
     
    // don't you just hate this fucked up mix of logic and presentation?
    $table_style  = "background-color: #f0f0f0; font-size: 11px; text-align: center; margin-top: 20px";
    $td_style     = "padding: 2px 5px 2px 5px; border-top: white 2px solid;";
    $h3_style     = "padding-left: 5px; font-size: 15px; background-color: white; font-family: Arial,Verdana,sans-serif,serif";

    $brochures = get_option('rr_brochures');
    if (!$brochures)
        return;

    if (count($brochures) == 0)
        return;

    assert(count($brochures) % 3 == 0);

    for ($i = 0; $i < rr_brochures_nb_brochures(); ++$i) {
        if (isset($brochures["post_url_$i"]) && 
            isset($brochures["img_url_$i"]) &&
            isset($brochures["ext_url_$i"])) {

            $post_url = $brochures["post_url_$i"];
            $img_url  = $brochures["img_url_$i"];
            $ext_url  = $brochures["ext_url_$i"];

            $cells[$i]  = "<a href='$ext_url'><img src='$img_url'/><br/><br/></a>";
            $cells[$i] .= "<a href='$post_url'>Lees de korte inhoud</a><br/>";
            $cells[$i] .= "<a href='$ext_url'>Bestel de brochure</a>";
        }
        else {
            $cells[$i] = "";
        }
        $cells[$i] = "<td width=140>$cells[$i]</td>";
    }

    /* Create the table */
    $table = "<table style='$table_style' width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>";
    $table .= "<tr><td style='padding: 20px 0px 0px 0px; font-size: 13px;' colspan='$in_a_row' align='left'>";
    $table .= "<h3 style='$h3_style'/>&raquo; Bestel hier je reismagazines of brochures.</h3></td></tr>";

    $row = "";
    for ($i = 1; $i <= rr_brochures_nb_brochures(); $i++) {
        $row .= $cells[$i-1];

        if ($i % $in_a_row == 0) {
            $table .= "<tr>$row</tr>";
            $row = "";
        }
    }
    $table .= '</table>';

    echo $table;
}

function rr_brochures_menu()
{
    add_posts_page('Reisreporter Brochures', 'Reisreporter Brochures',
                   'manage_options', 'rr_brochures', 'rr_brochures_show_admin');
}

function rr_brochures_show_admin()
{
    global $post;
    $inputs = array();

    if (!current_user_can('manage_options')) {
        wp_die( __('You do not have sufficient permissions to access this page.') );
    }

    if (isset($_POST['submit'])) {
        rr_brochures_save($_POST);
        echo "<div id='message' class='updated fade'><p><strong>Brochures saved!</strong></p></div>";
    }

    $brochures = get_option('rr_brochures');

    /* Create the input text fields. */
    for ($i = 0; $i < rr_brochures_nb_brochures(); $i++) {
        $input = $post_url = $img_url = $ext_url = "";

        if ($brochures != false && isset($brochures["post_url_$i"])) {
            $post_url = $brochures["post_url_$i"];
            $img_url  = $brochures["img_url_$i"];
            $ext_url  = $brochures["ext_url_$i"];
        }

        $input .= "<tr>";
        $input .= "<th>Post url:</th>";
        $input .= "<td><input size='120' type='text' name='post_url_$i' value='$post_url'/></td>";
        $input .= "</tr>";

        $input .= "<tr>";
        $input .= "<th>Image url:</th>";
        $input .= "<td><input size='120' type='text' name='img_url_$i' value='$img_url'/></td>";
        $input .= "</tr>";

        $input .= "<tr>";
        $input .= "<th>External url:</th>";
        $input .= "<td><input size='120' type='text' name='ext_url_$i' value='$ext_url'/></td>";
        $input .= "</tr>";

        $inputs[$i] = $input;
    }
?>
    <div class="wrap">
        <h2>Reisreporter Brochures</h2>'
        <form action="" method="post">
            <div align='left' style="background-color: #eee; margin: 5px; padding: 5px;">
                <?php for ($i = 0; $i < rr_brochures_nb_brochures(); ++$i): ?>
                    <table style='width: 100%;'>
                        <tr>
                            <th colspan='2' style="background-color: #ddd"><strong>Brochure #<?php echo $i+1; ?></strong></th>
                        </tr>
                        <?php echo $inputs[$i]; ?>
                    </table>
                <?php endfor; ?>
            </div>
            <p class="submit">
                <input style="margin-left: 40px;" type="submit" value="Submit" name="submit"/>
            </p>
        </form>
    </div>
<?php
}
