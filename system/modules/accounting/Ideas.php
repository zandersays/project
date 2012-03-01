<?php
class AccountingModule extends Module {

    public static function activate() {

    }

    public static function checkDependencies() {

    }

    public static function deactivate() {

    }

    public static function delete() {

    }

    public static function getAuthors() {

    }

    public static function getClasses() {

    }

    public static function getControlNavigation() {
        return array(
            'Accounting' => array(
                'Reports' => array(
                    'General Ledger',
                    'Profit and Loss',
                    'Balance Sheet',
                    'Statement of Cash Flows',
                    'Aged Receivables',
                    'Custom',
                ),
                'Accounts Receivable' => array(
                    'Invoices',
                    'Post Charges',
                    'Receipts',
                ),
                'Accounts Payable' => array(
                    'Unentered Bills',
                    'Enter Bills',
                    'Pay Bills',
                    'Vendors',
                ),
                'General Ledger' => array(
                    'Journal Entries',
                    'Banking Reconciliation',
                ),
                'Settings' => array(
                    'Accounting Settings', // Cash or accrual accounting
                    'Policies and Controls',
                    'Banking',
                    'Chart of Accounts',
                    'Charge Codes',
                ),
        ));
    }

    public static function getDefaultSettings() {

    }

    public static function getDependencies() {

    }

    public static function getDescription() {

    }

    public static function getPermissions() {

    }

    public static function getName() {
        return 'Accounting';
    }

    public static function getUrl() {

    }

    public static function getVersion() {

    }

    public static function install() {

    }

    public static function load($settings) {

    }

    public static function uninstall() {

    }

}
?>