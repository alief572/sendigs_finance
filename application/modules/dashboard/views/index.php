<?php
$permission_app_pr_depart_finance = 'Approval_PR_Depart_Finance.View';
$permission_app_pr_depart_management = 'Approval_PR_Depart_Management.View';
$permission_app_pr_stock = 'Approval_PR_Stock_Management.View';
$permission_app_pr_asset = 'Approval_PR_Asset_Management.View';
$permission_app_transport = 'Pengajuan_Transportasi_Approval.View';
$permission_app_kasbon_finance = 'Kasbon_Approval.View';
$permission_app_kasbon_management = 'Approval Kasbon Management.View';
$permission_app_expense_finance = 'Expense_Approval.View';
$permission_app_expense_management = 'Approval Expense Management.View';
$permission_app_pembayaran_periodik = 'Approval_Pengajuan_Pembayaran_Rutin.View';
?>
<style>
	.card {
		border: 1px solid #ddd;
		border-radius: 4px;
		padding: 15px;
		margin-bottom: 20px;
		background-color: #fff;
		box-shadow: 0 2px 2px rgba(0, 0, 0, 0.05);
	}

	.card-title {
		font-weight: bold;
		margin-bottom: 10px;
		font-size: 20px;

	}
</style>
<div class="box">
	<div class="box-header">
	</div>
	<div class="box-body">
		<div class="row">
			<?php
			if (has_permission($permission_app_pr_depart_finance)) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('non_rutin/app_pr_dept_finance') ?>">
						<div class="card bg-red">
							<div class="card-title">PR Dept: Finance Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_pr_dept_finance']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}
			if (has_permission($permission_app_pr_depart_management)) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('non_rutin/approval_management') ?>">
						<div class="card bg-red">
							<div class="card-title">PR Dept: Management Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_pr_dept_management']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}
			?>

			<?php
			if (has_permission($permission_app_pr_stock)) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('app_pr_stock/approval_management') ?>">
						<div class="card bg-yellow">
							<div class="card-title">Approval PR Stock</div>
							<h2><?= number_format($all_ttl['ttl_app_pr_stok']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}
			?>
			<?php
			if (has_permission($permission_app_pr_asset)) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('pr_asset/approval_management') ?>">
						<div class="card bg-green">
							<div class="card-title">Approval PR Asset</div>
							<h2><?= number_format($all_ttl['ttl_app_pr_asset']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if (has_permission($permission_app_transport)) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('expense/transport_req_fin') ?>">
						<div class="card bg-blue">
							<div class="card-title">Approval Transport</div>
							<h2><?= number_format($all_ttl['ttl_app_transport']) ?></h2>
						</div>
					</a>
				</div>
			<?php

			}
			if (has_permission($permission_app_kasbon_finance)) {
			?>
				<div class="col-md-4">
					<a href="expense/kasbon_fin">
						<div class="card bg-purple">
							<div class="card-title">Kasbon: Finance Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_kasbon_finance']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if (has_permission($permission_app_kasbon_management)) {
			?>
				<div class="col-md-4">
					<a href="expense/kasbon_fin_manage">
						<div class="card bg-purple">
							<div class="card-title">Kasbon: Management Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_kasbon_management']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if (has_permission($permission_app_expense_finance)) {
			?>
				<div class="col-md-4">
					<a href="expense/list_expense_approval">
						<div class="card bg-light-blue">
							<div class="card-title">Expense: Finance Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_expense_finance']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if (has_permission($permission_app_expense_management)) {
			?>
				<div class="col-md-4">
					<a href="expense/list_expense_approval_manage">
						<div class="card bg-light-blue">
							<div class="card-title">Expense: Management Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_expense_management']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if (has_permission($permission_app_pembayaran_periodik)) {
			?>
				<div class="col-md-4">
					<a href="pengajuan_rutin/app_list">
						<div class="card bg-gray">
							<div class="card-title">Approval Pengajuan Periodik</div>
							<h2><?= number_format($all_ttl['ttl_app_periodik']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}
			?>
		</div>
	</div>
</div>