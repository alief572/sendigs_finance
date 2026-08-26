<?php if(empty($files)): ?>
    <div class="alert alert-warning text-center"><i class="fa fa-info-circle"></i> Tidak ada file evidence pada dokumen ini.</div>
<?php else: ?>
    <table class="table table-bordered table-striped table-hover">
        <thead>
            <tr class="bg-blue">
                <th class="text-center" width="5%">#</th>
                <th width="50%">Nama File Evidence</th>
                <th class="text-center" width="15%">Tipe File</th>
                <th class="text-center" width="15%">Ukuran File</th>
                <th class="text-center" width="15%">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 0;
            foreach($files as $f): 
                $no++;
                $size_str = '-';
                if (!empty($f->file_size)) {
                    if ($f->file_size >= 1048576) {
                        $size_str = round($f->file_size / 1048576, 2) . ' MB';
                    } else {
                        $size_str = round($f->file_size / 1024, 2) . ' KB';
                    }
                }
            ?>
                <tr>
                    <td class="text-center"><?= $no ?></td>
                    <td>
                        <i class="fa fa-paperclip text-primary"></i> <b><?= htmlspecialchars($f->file_name) ?></b>
                    </td>
                    <td class="text-center">
                        <span class="label label-info"><?= strtoupper($f->file_type ?? 'FILE') ?></span>
                    </td>
                    <td class="text-center"><?= $size_str ?></td>
                    <td class="text-center">
                        <a href="<?= base_url($f->file_path) ?>" target="_blank" class="btn btn-xs btn-success" title="Buka File">
                            <i class="fa fa-external-link"></i> Buka File
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
