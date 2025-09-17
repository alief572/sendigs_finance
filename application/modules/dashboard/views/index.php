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
			<div class="col-md-4">
				<a href="<?= base_url('non_rutin/approval_management') ?>">
					<div class="card bg-red">
						<div class="card-title">Approval PR Department</div>
						<h2><?= number_format($all_ttl['ttl_app_pr_dept']) ?></h2>
					</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="<?= base_url('app_pr_stock/approval_management') ?>">
					<div class="card bg-yellow">
						<div class="card-title">Approval PR Stock</div>
						<h2><?= number_format($all_ttl['ttl_app_pr_stok']) ?></h2>
					</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="<?= base_url('pr_asset/approval_management') ?>">
					<div class="card bg-green">
						<div class="card-title">Approval PR Asset</div>
						<h2><?= number_format($all_ttl['ttl_app_pr_asset']) ?></h2>
					</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="<?= base_url('expense/transport_req_fin') ?>">
					<div class="card bg-blue">
						<div class="card-title">Approval Transport</div>
						<h2><?= number_format($all_ttl['ttl_app_transport']) ?></h2>
					</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="expense/kasbon_fin">
					<div class="card bg-purple">
						<div class="card-title">Approval Kasbon</div>
						<h2><?= number_format($all_ttl['ttl_app_kasbon']) ?></h2>
					</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="expense/list_expense_approval">
					<div class="card bg-light-blue">
						<div class="card-title">Approval Expense</div>
						<h2><?= number_format($all_ttl['ttl_app_expense']) ?></h2>
					</div>
				</a>
			</div>
			<div class="col-md-4">
				<a href="pengajuan_rutin/app_list">
					<div class="card bg-gray">
						<div class="card-title">Approval Pengajuan Periodik</div>
						<h2><?= number_format($all_ttl['ttl_app_periodik']) ?></h2>
					</div>
				</a>
			</div>
		</div>
	</div>
</div>