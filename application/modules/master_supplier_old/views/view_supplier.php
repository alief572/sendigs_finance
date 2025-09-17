<?php

if (!empty($this->uri->segment(3))) {
  $getC             = $this->db->get_where('master_supplier', array('id_supplier' => $id))->row();
  $getCur           = $this->db->get('mata_uang')->result();
  $PIC_Office       = $this->db->get_where('child_supplier_pic_office', array('id_supplier' => $id))->result();
  $PIC_Factory      = $this->db->get_where('child_supplier_pic_factory', array('id_supplier' => $id))->result();
  $PIC_Excompany    = $this->db->get_where('child_supplier_pic_excompany', array('id_supplier' => $id))->result();
  $name_type        = $this->db->get_where('child_supplier_type', array('id_type' => $getC->id_type))->row();
  $country          = $this->db->get_where('negara', array('id' => $getC->id_country))->row();
  $prov             = $this->db->get_where('provinsi', array('id_prov' => $getC->id_prov))->row();
  $city             = $this->db->get_where('kabupaten', array('id_kab' => $getC->city_office))->row();
  $name_supcap      = $this->db->where_in('id_capacity', explode(";", $getC->id_capacity))->get('child_supplier_capacity')->result();
  $name_procat      = $this->db->get_where('master_product_category', array('id_category' => $getC->id_product_category))->row();
  $name_buscat      = $this->db->get_where('child_supplier_business_category', array('id_business' => $getC->id_business))->row();
  $name_toq         = $this->db->get_where('child_supplier_toq', array('id_toq' => $getC->id_toq))->row();
  $getSP            = $this->db->get_where('child_supplier_pic', array('id_supplier' => $id))->result();
  $getSB            = $this->db->get_where('child_supplier_bank', array('id_supplier' => $id))->result();
  //$getB     = $this->db->get_where('master_product_brand',array('id_supplier'=>$id))->result();
}

?>

<div class="content">
  <legend class="legend"><strong><?= $getC->nm_supplier_office ?></strong></legend>
  <!-- <span class="pull-left"><?= $getC->id_supplier ?></span> -->
  <!-- <hr> -->

  <div class="nav-tabs-custom">
    <ul class="nav nav-tabs nav-justified">
      <li class="active">
        <a href="#office" aria-controls="office" role="tab" data-toggle="tab"><strong>Office</strong></a>
      </li>
      <li>
        <a href="#factory" aria-controls="tab" role="tab" data-toggle="tab"><strong>Factory</strong> </a>
      </li>
      <li>
        <a href="#excompany" aria-controls="tab" role="tab" data-toggle="tab"><strong>Export Company</strong> </a>
      </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="office">
        <h4 style="margin-bottom:1em"><strong>Detail Supplier Office</strong></h4>
        <div class="row" style="line-height:15px">
          <div class="col-md-6">
            <table class="table-condensed" width="100%">
              <tr>
                <td width="30%"><label for="">ID Supplier</label></td>
                <td><label for="">:</label> <?= $getC->id_supplier ?></td>
              </tr>
              <tr>
                <td><label for="">Input Date</label></td>
                <td><label for="">:</label> <?= $getC->id_supplier ?></td>
              </tr>
              <tr>
                <td><label for="">Shipping</label></td>
                <td><label for="">:</label> <?= $getC->supplier_shipping ?></td>
              </tr>
              <tr>
                <td><label for="">Telephone</label></td>
                <td><label for="">:</label> <?= $getC->telephone_office_1 . ", " . $getC->telephone_office_2 ?></td>
              </tr>
              <tr>
                <td><label for="">Fax</label></td>
                <td><label for="">:</label> <?= $getC->fax_office ?></td>
              </tr>
              <tr>
                <td><label for="">Owner</label></td>
                <td><label for="">:</label> <?= $getC->owner ?></td>
              </tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table-condensed" width="100%">
              <tr>
                <td width="30%"><label for="">Country</label></td>
                <td><label for="">:</label> <?= $country->nm_negara ?></td>
              </tr>
              <tr>
                <td><label for="">Province</label></td>
                <td><label for="">:</label> <?= $prov->nama ?></td>
              </tr>
              <tr>
                <td><label for="">City</label></td>
                <td><label for="">:</label> <?= $city->nama ?></td>
              </tr>
              <tr>
                <td><label for="">Address</label></td>
                <td><label for="">:</label> <?= $getC->address_office ?></td>
              </tr>
              <tr>
                <td><label for="">ZIP Code</label></td>
                <td><label for="">:</label> <?= $getC->zip_code_office ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="tab-pane" id="factory">...</div>
      <div class="tab-pane" id="excompany">...</div>
    </div>
  </div>

  <h4 style="margin:1em auto"><strong>Detail Supplier Productivity</strong></h4>
  <div class="row">
    <div class="col-md-6">
      <table class="table-condensed" width="100%" style="line-height:15px">
        <tr>
          <td width="30%"><label for="">Supplier Type</label></td>
          <td><label for="">:</label> <?= $name_type->name_type ?></td>
        </tr>
        <tr>
          <td><label for="">Busines Catregory</label></td>
          <td><label for="">:</label> <?= $name_buscat->name_business ?></td>
        </tr>
        <tr>
          <td><label for="">Capacity</label></td>
          <td><label for="">:</label>
            <?php foreach ($name_supcap as $key) {
              echo $key->name_capacity . ", <br> ";
            } ?>
          </td>
        </tr>
        <tr>
          <td><label for="">Product Category</label></td>
          <td><label for="">:</label> <?= $name_procat->name_category ?></td>
        </tr>
        <tr>
          <td><label for="">Agent Name</label></td>
          <td><label for="">:</label> <?= $getC->agent_name ?></td>
        </tr>
        <tr>
          <td><label for="">Remarks</label></td>
          <td><label for="">:</label> <?= $getC->remarks ?></td>
        </tr>
      </table>
    </div>
    <div class="col-md-6">
      <table class="table-condensed" width="100%" style="line-height:15px">
        <tr>
          <td width="30%"><label for="">Website</label></td>
          <td><label for="">:</label>
            <a href="https://<?= $getC->website ?>">
              <?= $getC->website ?>
              <i class="fa fa-external-link"></i>
            </a>
          </td>
        </tr>
        <tr>
          <td><label for="">NPWP Number</label></td>
          <td><label for="">:</label> <?= $prov->nama ?></td>
        </tr>
        <tr>
          <td><label for="">NPWP Name</label></td>
          <td><label for="">:</label> <?= $city->nama ?></td>
        </tr>
        <tr>
          <td><label for="">NPWP Address</label></td>
          <td><label for="">:</label> <?= $getC->npwp_address ?></td>
        </tr>
        <tr>
          <td><label for="">Supplier Status</label></td>
          <td><label for="">:</label> <?= $getC->activation ?></td>
        </tr>
        <tr>
          <td><label for="">Status Factory</label></td>
          <td><label for="">:</label> <?= $getC->activation_factory ?></td>
        </tr>
      </table>
    </div>
  </div>
</div>
<!-- [id_type]=> ST00002
  [id_capacity] => SCY0006
  [id_business] => SBC0009
  [id_product_category] => PCN0022

  [website] => dghgfds
  [npwp] => 32453
  [npwp_name] => Dfgdsa
  [npwp_address] => dfghfds
  [nm_supplier_factory] => ASTE LANDWEHR ( Aste Landwehr Textil GmbH )
  [id_country_factory] => 101
  [id_prov_factory] => 12
  [city_factory] => 1205
  [address_factory] => Industriestrasse 10 Industriestrasse 10
  [zip_code_factory] => 73441
  [telephone_factory_1] => +49-736295693
  [telephone_factory_2] => 334-345
  [fax_factory] => +49 7362 305 0
  [owner_factory] => -
  [nm_supplier_excompany] => ASTE LANDWEHR ( Aste Landwehr Textil GmbH )
  [id_country_excompany] => 101
  [id_prov_excompany] => 16
  [city_excompany] => 1605
  [address_excompany] => Industriestrasse 10 Industriestrasse 10
  [zip_code_excompany] => 73441
  [telephone_excompany_1] => +49-736295693
  [telephone_excompany_2] => 345-345
  [fax_excompany] => +49 7362 305 0
  [owner_excompany] => -
  [remarks] => -
  [agent_name] => -
  [activation_factory] => active
  [activation] => active
  [created_by] => 2
  [created_on] => 2020-03-10 11:34:48
  [modified_by] => 1
  [modified_on] => 2020-04-18 12:01:50 -->