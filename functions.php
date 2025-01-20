<?php

// liste des champs de la table des transactions
function get_champs_transaction() {
    return array(
        "id" => array(
            "label" => "Identifiant",
            "type" => "primary_key",
            "hidden" => true
        ), 
        "date_saisie" => array(
            "label" => "Date de saisie",
            "type" => "date",
            "hidden" => true
        ), 
        "wp_user" => array(
            "label" => "Utilisateur",
            "type" => "index",
            "hidden" => true
        ), 
        "date_transaction" => array(
            "label" => "Date de la transaction",
            "type" => "date",
        ), 
        "montant" => array(
            "label" => "Montant",
            "type" => "number",
        ), 
        "type" => array(
            "label" => "Type de transaction",
            "type" => "select",
            "options" => get_types_transaction(),
        ), 
        "facture" => array(
            "label" => "Facture",
            "type" => "text",
        )
    );
}

// liste des types de transactions qui existent
function get_types_transaction() {
    return array(
        1 => array(
            "class" => "depense",
            "label" => "Dépense",
        ),
        2 => array(
            "class" => "revenu",
            "label" => "Revenu",
        ),
    );
}

// afficher la liste des transactions
add_shortcode( 'liste_transactions', 'function_liste_transactions' );
function function_liste_transactions( $atts ) {
    $output = "";

    global $wpdb;

    $req = "SELECT * FROM exo_transaction";
    $rows = $wpdb->get_results( $req );
    $nb = count($rows);
    $output .= "Il y a $nb transactions au total";

    $class = "mobile";
    if (!wp_is_mobile()) $class = "desktop";


    $champs = get_champs_transaction();

    $output .= "
    <table class='table $class'>
        <thead>
            <tr class='table-head'>";

    $output .= "<th>Saisie</th>
                <th>Montant</th>
                <th>Facture</th>";

    $output .= "  
            </tr>
        </thead>
        <tbody>";

        foreach( $rows as $row ) {

            $date_transaction_obj = strtotime($row -> date_transaction);
            $date_transaction =  date('l N F, Y', $date_transaction_obj);
            $utilisateur = get_userdata( $row -> wp_user ) -> display_name;
            $montant = $row -> montant;
            $type = $row -> type;
            $facture = $row -> facture;

            $class_row = get_types_transaction()[$row -> type]["class"];

            $output .= "<tr class='$class_row'>
                            <td class='saisie'>
                                <span class='date'>" . $date_transaction . "</span>
                                <span class='utilisateur'>" . $utilisateur . "</span>
                            </td>
                            <td class='montant'>";
                            if ($type == 1) {
                                $montant = "-" . $montant;
                            }
                            $output .= $montant . "</td>
                            <td class='facture'>" . $facture . "</td>
                        </tr>";
        }
    $output .="
        </tbody>
    </table>
    ";
   
    ?>

    <h1>Liste des transactions</h1>

    <?php

    return $output;
}


// afficher la liste des transactions
add_shortcode( 'saisie_transaction', 'function_saisie_transaction' );
function function_saisie_transaction( $atts ) {
    $output = "";

    $champs = get_champs_transaction();

    $output .= "<form action='/saisie' method='post' class='formulaire'>";

    global $wpdb;
    $last_id = $wpdb->get_row( "SELECT max(id) as id FROM exo_transaction" );


    
    foreach( $champs as $key => $value ) {
        // echo "<pre>"; 
        //     var_dump($key); echo "</pre>";
        // echo "<pre>"; 
        //     var_dump($value); 
        // echo "</pre>";
        $valeur = "Ce champ est caché";


        if (isset($value["hidden"])){

            switch ($value["type"]) {
                case 'primary_key':
                    $valeur = $last_id->id + 1;
                break;

                case 'date':
                    $valeur = date('Y-m-d');
                break;

                case 'index':
                    $valeur = get_current_user_id();
                break;

                default:
                    $valeur = "Erreur dans la saisie";
                break;
            }

            $output .= "
            <div>
            
                <label for='$key'>" . $value["label"] . "</label>
                <span>" . $valeur . "</span>
            </div>";

        } else {  
            if (isset ($_POST))
                {
                    $valeur = htmlspecialchars( $_POST[$key] );
                }   
            
            switch ($value['type']) {
                case 'select':
                    $input = "<select id='$key' name='$key'>";
                    foreach ($value['options'] as $option => $o) {
                        $input .= "<option value='" . $option;
                        if ($option == $valeur) {
                            $input .= "selected";
                        }
                        $input .= "{$o['label']}</option>";
                    }
                    $input .= "</select>";
                break;

                default:
                    $valeur = false;                
                    $input = "<input value='$valeur' type='" . $value["type"] . "' id='$key' name='$key'>";
                break;
            }
            $output .= "<div>
                <label for='$key'>" . $value["label"] . "</label>
                $input
            </div>";
        };

    }

    $output .= "
    <div>
        <input type='submit' value='Enregistrer'>
    </div>
    </form>";
    
    return $output;
}


add_shortcode( "exo_affichage", "function_exo_affichage" );
function function_exo_affichage( ) {
    include( "exo.php");
    return exo_affichage_nouveau_fichier();
}