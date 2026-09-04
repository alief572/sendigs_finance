<?php
$current_username = '';
$user_id = '';
if (isset($this->auth)) {
	$current_username = strtolower($this->auth->user_name());
	$user_id = $this->auth->user_id();
} elseif ($this->session->userdata('app_session')) {
	$session = $this->session->userdata('app_session');
	$current_username = isset($session['username']) ? strtolower($session['username']) : '';
	$user_id = isset($session['id_user']) ? $session['id_user'] : '';
}

$is_admin = ($current_username === 'admin' || $user_id == 7 || (isset($this->auth) && method_exists($this->auth, 'is_admin') && $this->auth->is_admin()));
$is_finance = ($current_username === 'finance' || $user_id == 203);
$is_imanuel = ($current_username === 'imanuel' || $user_id == 96);
?>
<link rel="stylesheet" href="<?= base_url('assets/css/dashboard-cards.css?v=' . (file_exists(FCPATH . 'assets/css/dashboard-cards.css') ? filemtime(FCPATH . 'assets/css/dashboard-cards.css') : time())) ?>">

<div class="box">

	<div class="box-body">
		<div class="row">
			<?php
			if ($is_admin || $is_finance) {
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
			if ($is_admin || $is_imanuel) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('non_rutin/approval_management') ?>">
						<div class="card bg-crimson">
							<div class="card-title">PR Dept: Management Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_pr_dept_management']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}
			?>

			<?php
			if ($is_admin || $is_finance) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('app_pr_stock/approval_management') ?>">
						<div class="card bg-amber">
							<div class="card-title">Approval PR Stock</div>
							<h2><?= number_format($all_ttl['ttl_app_pr_stok']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}
			?>
			<?php
			if ($is_admin || $is_imanuel) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('pr_asset/approval_management') ?>">
						<div class="card bg-emerald">
							<div class="card-title">Approval PR Asset</div>
							<h2><?= number_format($all_ttl['ttl_app_pr_asset']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if ($is_admin || $is_finance) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('expense/transport_req_fin') ?>">
						<div class="card bg-sky-blue">
							<div class="card-title">Approval Transport</div>
							<h2><?= number_format($all_ttl['ttl_app_transport']) ?></h2>
						</div>
					</a>
				</div>
			<?php

			}
			if ($is_admin || $is_finance) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('expense/kasbon_fin') ?>">
						<div class="card bg-violet">
							<div class="card-title">Kasbon: Finance Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_kasbon_finance']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if ($is_admin || $is_imanuel) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('expense/kasbon_fin_manage') ?>">
						<div class="card bg-orchid">
							<div class="card-title">Kasbon: Management Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_kasbon_management']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if ($is_admin || $is_finance) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('expense/list_expense_approval') ?>">
						<div class="card bg-teal">
							<div class="card-title">Expense: Finance Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_expense_finance']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if ($is_admin || $is_imanuel) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('expense/list_expense_approval_manage') ?>">
						<div class="card bg-cyan">
							<div class="card-title">Expense: Management Approval</div>
							<h2><?= number_format($all_ttl['ttl_app_expense_management']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if ($is_admin || $is_imanuel) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('pengajuan_rutin/app_list') ?>">
						<div class="card bg-slate">
							<div class="card-title">Approval Pengajuan Periodik</div>
							<h2><?= number_format($all_ttl['ttl_app_periodik']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if ($is_admin || $is_finance) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('asset_planning/index_asset/approve') ?>">
						<div class="card bg-indigo">
							<div class="card-title">Approval Asset Budget Finance</div>
							<h2><?= number_format($all_ttl['ttl_app_asset_budget_finance']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}

			if ($is_admin || $is_imanuel) {
			?>
				<div class="col-md-4">
					<a href="<?= base_url('asset_planning/index_asset_management/approve') ?>">
						<div class="card bg-coral">
							<div class="card-title">Approval Asset Budget Management</div>
							<h2><?= number_format($all_ttl['ttl_app_asset_budget_management']) ?></h2>
						</div>
					</a>
				</div>
			<?php
			}
			?>
		</div>
	</div>
</div>