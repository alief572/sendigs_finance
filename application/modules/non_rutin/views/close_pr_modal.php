<div class="col-md-12">
    <input type="hidden" name="no_pengajuan" value="<?= $no_pengajuan ?>">
    <div class="form-group">
        <label style="font-size:11px; font-weight:700; color:#777; text-transform:uppercase;">No. PR</label>
        <input type="text" class="form-control form-control-sm" value="<?= $no_pr ?>" readonly style="background:#f5f5f5; font-weight:600; color:#333;">
    </div>
    <div class="form-group">
        <label style="font-size:11px; font-weight:700; color:#777; text-transform:uppercase;">Alasan Closing PR <span class="text-danger">*</span></label>
        <textarea name="close_pr_reason" class="form-control form-control-sm" rows="3" placeholder="Masukkan alasan closing PR..." required style="resize:none;"></textarea>
    </div>
    <div class="alert alert-warning" style="padding:8px 12px; font-size:12px; margin-bottom:0;">
        <i class="fa fa-exclamation-triangle"></i>
        PR yang sudah di-close <strong>tidak dapat dibuka kembali</strong>.
    </div>
</div>