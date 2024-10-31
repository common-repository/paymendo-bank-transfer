<?php
/**
 * Created by PhpStorm.
 * File: header.php
 * Project: paymendo-bank-transfer
 * Year: 2021
 */
?>
<div class="paymendo-bank-transfer-container">
    <div class="col-md-10 offset-md-1">
        <p>&nbsp;</p>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo03"
                    aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
                <ul class="navbar-nav mr-auto mt-2 mt-lg-0 w-100">
                    <li class="nav-item">
                        <a class="nav-link<?php
					echo ( filter_input( 1, 'page', FILTER_SANITIZE_STRING ) === 'paymendo-bank-transfer-lite' ) ? ' active' : ''
					?>" href="<?php echo esc_url(admin_url( 'admin.php?page=paymendo-bank-transfer-lite' )) ?>"><i
                                    class="fa fa-bank"></i> <?php _e('Bank Accounts','paymendo-bank-transfer-lite') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php
					echo ( filter_input( 1, 'page', FILTER_SANITIZE_STRING ) === 'paymendo-bank-transfer-payments' ) ? ' active' : ''
					?>"
                           href="<?php echo esc_url(admin_url( 'admin.php?page=paymendo-bank-transfer-payments' )) ?>"><i
                                    class="fa fa-list"></i> <?php _e('Payments', 'paymendo-bank-transfer-lite') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php
					echo ( filter_input( 1, 'page', FILTER_SANITIZE_STRING ) === 'paymendo-bank-transfer-settings' ) ? ' active' : ''
					?>"
                           href="<?php echo esc_url(admin_url( 'admin.php?page=paymendo-bank-transfer-settings' )) ?>"><i
                                    class="fa fa-cogs"></i> <?php _e('Settings', 'paymendo-bank-transfer-lite') ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" target="_blank" href="https://www.gri.net/clientarea.php"><i
                                    class="fa fa-question-circle"></i> <?php _e('License & Support', 'paymendo-bank-transfer-lite') ?></a>
                    </li>
                </ul>
                <div class="form-inline my-2 my-lg-0">
                    <a href="https://www.paymendo.com/" target="_blank">
                        <img src="<?php echo esc_attr(pbt_get_plugin_assets('images/paymendo-logo.png')) ?>"
                             alt="paymendo"
                             height="45"
                         />
                    </a>
                </div>
            </div>
        </nav>
        <div class="bg-white">
            <?php if(isset($page_title)||isset($page_description)): ?>
            <div class="plugin-page-header p-3 border-bottom bg-light d-flex">
            <div class="w-100">
                <?php if( isset($page_title) ) {echo '<h4>' . wp_kses_post($page_title) . '</h4>';} ?>
                <?php if( isset($page_description) ) {echo '<p>' . wp_kses_post($page_description) . '</p>';} ?>
            </div>
                <?php if(isset($page_extra)) {echo wp_kses_post($page_extra);}?>
            </div>
            <?php endif; ?>