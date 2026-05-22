Options -MultiViews
RewriteEngine On

ErrorDocument 404 /404.html

RewriteRule ^home(.*)?$ index.php [L]
RewriteRule ^logout(.*)?$ apps/sign_out.php [L]


### auth
RewriteRule ^change_password(.*)?$ apps/auth/views/do.change_password.phtml [L]
RewriteRule ^add_new_user(.*)?$ apps/auth/views/do.add_new_user.phtml [L]
RewriteRule ^add_agent_user(.*)?$ apps/auth/views/do.add_new_agent_user.phtml [L]
RewriteRule ^manage_agent_user(.*)?$ apps/auth/views/do.manage_agent_user.phtml [L]
RewriteRule ^myaccount(.*)?$ apps/auth/views/do.myaccount.phtml [L]
RewriteRule ^user_permissions(.*)?$ apps/auth/views/do.user_permissions.phtml [L]
RewriteRule ^sys_user_permissions(.*)?$ apps/auth/views/do.sys_user_permissions.phtml [L]




#### admin user #####
RewriteRule ^sys_user(.*)?$ apps/auth/views/do.add_new_sys_user.phtml [L]
RewriteRule ^manage_sys_user(.*)?$ apps/auth/views/do.manage_sys_user.phtml [L]
RewriteRule ^my_account(.*)?$ apps/auth/views/do.my_account.phtml [L]


####### Reports
RewriteRule ^agent_activities(.*)?$ apps/reports/views/this_agent_activities.phtml [L]
RewriteRule ^transactions_report(.*)?$ apps/reports/views/this_agent_transactions_report.phtml [L]
RewriteRule ^commissions_report(.*)?$ apps/reports/views/this_agent_commission_report.phtml [L]
RewriteRule ^vault_report(.*)?$ apps/reports/views/this_agent_vault_report.phtml [L]

RewriteRule ^activities_report(.*)?$ apps/reports/views/this_sys_activities_report.phtml [L]
RewriteRule ^sys_transactions_report(.*)?$ apps/reports/views/this_sys_transactions_report.phtml [L]
RewriteRule ^sys_commissions_report(.*)?$ apps/reports/views/this_sys_commissions_report.phtml [L]
RewriteRule ^custom_report(.*)?$ apps/reports/views/this_sys_custom_report.phtml [L]
RewriteRule ^agent_custom_dashboard(.*)?$ apps/reports/views/this_sys_agent_custom_dashboard.phtml [L]


## dashboard
RewriteRule ^admin(.*)?$ apps/dashboard/views/do.admin.phtml [L]
RewriteRule ^agent(.*)?$ apps/agents/views/do.agent.phtml [L]
RewriteRule ^teller(.*)?$ apps/transactions/views/do.teller.phtml [L]

######### agent management #########
RewriteRule ^setup_agent(.*)?$ apps/agents/views/do.setup_agent.phtml [L]
RewriteRule ^manage_agent(.*)?$ apps/agents/views/do.manage_agent.phtml [L]
RewriteRule ^credit_debit_agent(.*)?$ apps/agents/views/do.credit_debit_agent.phtml [L]


########## approvals ############# 
RewriteRule ^user_approval(.*)?$ apps/approvals/views/do.user_approval.phtml [L]
RewriteRule ^agency_setup_approval(.*)?$ apps/approvals/views/do.agency_setup_approval.phtml [L]
RewriteRule ^branch_setup_approval(.*)?$ apps/approvals/views/do.branch_setup_approval.phtml [L]
RewriteRule ^high_volume_transaction(.*)?$ apps/approvals/views/do.high_volume_transaction.phtml [L]


### branches
RewriteRule ^setup_branch(.*)?$ apps/branches/views/do.setup_branch.phtml [L]
RewriteRule ^manage_branch(.*)?$ apps/branches/views/do.manage_branch.phtml [L]

## analytics
RewriteRule ^sys_comparative_analytics(.*)?$ apps/analytics/views/do.sys_comparative_analytics.phtml [L]
RewriteRule ^comparative_analytics(.*)?$ apps/analytics/views/do.comparative_analytics.phtml [L]
RewriteRule ^cohort_analytics(.*)?$ apps/analytics/views/do.cohort_analytics.phtml [L]
RewriteRule ^churn_pattern(.*)?$ apps/analytics/views/do.churn_pattern.phtml[L]
RewriteRule ^lifetime_value(.*)?$ apps/analytics/views/do.lifetime_value.phtml[L]
RewriteRule ^summaries(.*)?$ apps/analytics/views/do.summaries.phtml[L]
RewriteRule ^segmentation(.*)?$ apps/analytics/views/do.segmentation.phtml[L]
RewriteRule ^what_if_analytics(.*)?$ apps/analytics/views/do.what_if_analytics.phtml [L]

##### transactions
RewriteRule ^branch_vault_cash(.*)?$ apps/transactions/views/do.branch_vault_cash.phtml [L]
RewriteRule ^credit_branch_e_cash(.*)?$ apps/transactions/views/do.credit_branch_e_cash.phtml [L]
RewriteRule ^sell_buy_cash(.*)?$ apps/transactions/views/do.sell_buy_cash.phtml [L]
RewriteRule ^new_transaction(.*)?$ apps/transactions/views/do.new_transaction.phtml [L]
RewriteRule ^verify_id(.*)?$ apps/transactions/views/do.id_verification.phtml [L]

########################### Callback ##########################
# RewriteRule ^transaction_call_back(.*)?$ apps/transactions/services/callback/transaction_call_back.php [L]


### settings ###
RewriteRule ^business_settings(.*)?$ apps/settings/views/do.business_settings.phtml [L]
RewriteRule ^system_settings(.*)?$ apps/settings/views/do.system_settings.phtml [L]
RewriteRule ^user_settings(.*)?$ apps/settings/views/do.user_settings.phtml [L]
RewriteRule ^set_transaction_limits(.*)?$ apps/settings/views/do.transaction_limit.phtml [L]


############# sanctions ################################# 
RewriteRule ^update_sanction_screen_list(.*)?$ apps/sanctions/views/do.update_sanction_screen_list.phtml [L]
RewriteRule ^sanction_hits(.*)?$ apps/settings/views/do.sanction_hits.phtml [L]
RewriteRule ^view_sanction_list(.*)?$ apps/settings/views/do.view_sanction_list.phtml [L]


################################# API #################################
RewriteRule ^api/login(.*)?$ api/auth/apiLogin.php [L]
RewriteRule ^api/cr(.*)?$ api/transactions/apiCRTransaction.php [L]
RewriteRule ^api/dr(.*)?$ api/transactions/apiDRTransaction.php [L]
RewriteRule ^api/branch_balances(.*)?$ api/transactions/apiBranchBalances.php [L]
RewriteRule ^api/vgc(.*)?$ api/transactions/apiVerifyClientGhanaCard.php [L]
RewriteRule ^api/nck(.*)?$ api/utils/apiNameCheck.php [L]
RewriteRule ^api/anunaki(.*)?$ api/transactions/apiDRStatus.php [L]



