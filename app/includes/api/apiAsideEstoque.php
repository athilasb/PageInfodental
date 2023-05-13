<?php
//estoque

if(isset($apiConfig['estoque'])){
?>

    <section class="aside aside-form" id="iside-estoque">
    <div class="aside__inner1 aside__inner9">
        <input type="hidden" name="alteracao" value="0">
        <header class="aside-header">
            <h1 class="js-titulo"> Pagamento Avulso</h1>
            <a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
        </header>
        <form action="" method="post" class="aside-content form">
            <section class="filter">
				<div class="filter-group">
					<div class="filter-title">	
					</div>
				</div>
				<div class="filter-group">
					<div class="filter-title">
						<p><a href="" class="button button_main"><span class="iconify" data-icon="material-symbols:check-small" style="color: #fecea2;" data-width="24"></span><span>Salvar</span></a></p>
					</div>
				</div>
			</section>
            <fieldset>
                <legend>Definir fatura</legend>	
                    <div>
                    <section class="filter" style="width: 100%;">
                        <div class="grid grid_3" style="grid-template-columns: repeat(5, 1fr);width: 100%;">
                            <div class="filter-title">
                                <p>Produto</p>
                                <strong>Luva de Látex</strong> 
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Produto</p>
                                    <strong>Luva de Látex</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Produto</p>
                                    <strong>Luva de Látex</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Produto</p>
                                    <strong>Luva de Látex</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Produto</p>
                                    <strong>Luva de Látex</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Estoque min.</p>
                                    <strong>Luva de Látex</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Estoque atual</p>
                                    <strong>PP</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Menor preço</p>
                                    <strong>SuperMax</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Último preço</p>
                                    <strong>Unidade</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Categoria</p>
                                    <strong>Insumo geral</strong> 
                                </div>
                            </div>
                            <div class="filter-group">
                                <div class="filter-title">
                                    <p>Cálculo</p>
                                    <strong>Hora clínica</strong> 
                                </div>
                                </div>
                                <div class="filter-group">
                                    </div>
                            </div>
                    </section>  
            </fieldset>

            <fieldset>
                <legend>Estoque</legend>
                <section>
                    <table style="width: 100%;">

                        <thead>
                            <tr>
                                <th style="text-align: left;">Local armazenado</th>
                                <th style="text-align: left;">Lote</th>
                                <th style="text-align: left;">Vencimento</th>
                                <th style="text-align: left;">Quantidade</th>
                                <th style="text-align: left;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Consultório 1</td>
                                <td>0001</td>
                                <td>12/01/2023</td>
                                <td>2</td>
                                <td>
                                    <div class="filter-form form">
                                        <div class="" style="display: flex;max-width: 130px;margin: 10px 0px;">						
                                            <a href="" class="button button_main" style="border-radius: 0px;">-</a>
                                            <input type="text" name="" value="1" style="border-radius: 0px;width: 30px;">
                                            <a href="" class="button button button_main" style="border-radius: 0px;">+</a>
                                        </div>
                                        <a href="" class="button button_main" style="border-radius: 0px;margin: 10px 0px;"><span class="iconify" data-icon="fluent:arrow-exit-20-filled" style="color: white;" data-width="24"></span></a>
                                        <a href="" class="button button_main" style="border-radius: 0px; margin: 10px 0px;"><span class="iconify" data-icon="fluent:arrow-repeat-all-16-regular" style="color: white;" data-width="24"></span></a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Consultório 1</td>
                                <td>0001</td>
                                <td>12/01/2023</td>
                                <td>2</td>
                                <td>
                                    <div class="filter-form form">
                                        <div class="" style="display: flex;max-width: 130px;margin: 10px 0px;">						
                                            <a href="" class="button button_main" style="border-radius: 0px;">-</a>
                                            <input type="text" name="" value="1" style="border-radius: 0px;width: 30px;">
                                            <a href="" class="button button button_main" style="border-radius: 0px;">+</a>
                                        </div>
                                        <a href="" class="button button_main" style="border-radius: 0px;margin: 10px 0px;"><span class="iconify" data-icon="fluent:arrow-exit-20-filled" style="color: white;" data-width="24"></span></a>
                                        <a href="" class="button button_main" style="border-radius: 0px; margin: 10px 0px;"><span class="iconify" data-icon="fluent:arrow-repeat-all-16-regular" style="color: white;" data-width="24"></span></a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Consultório 1</td>
                                <td>0001</td>
                                <td>12/01/2023</td>
                                <td>2</td>
                                <td>
                                    <div class="filter-form form">
                                        <div class="" style="display: flex;max-width: 130px;margin: 10px 0px;">						
                                            <a href="" class="button button_main" style="border-radius: 0px;">-</a>
                                            <input type="text" name="" value="1" style="border-radius: 0px;width: 30px;">
                                            <a href="" class="button button button_main" style="border-radius: 0px;">+</a>
                                        </div>
                                        <a href="" class="button button_main" style="border-radius: 0px;margin: 10px 0px;"><span class="iconify" data-icon="fluent:arrow-exit-20-filled" style="color: white;" data-width="24"></span></a>
                                        <a href="" class="button button_main" style="border-radius: 0px; margin: 10px 0px;"><span class="iconify" data-icon="fluent:arrow-repeat-all-16-regular" style="color: white;" data-width="24"></span></a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </fieldset>
        </form>
    </section>

<script>
    $(".js-iside-estoque").click(() => {

        $("#iside-estoque").fadeIn(100, function() {
            $("#iside-estoque .aside__inner9").addClass("active");
        });
    });
</script>

<?php
}






//compras
if(isset($apiConfig['compras'])){
    ?>
    
        <section class="aside aside-form" id="iside-estoque">
        <div class="aside__inner1 aside__inner9">
            <input type="hidden" name="alteracao" value="0">
            <header class="aside-header">
                <h1 class="js-titulo">Compras</h1>
                <a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
            </header>
            <form action="" method="post" class="aside-content form">
                <fieldset>
                        <legend>Definir fatura</legend>	
                        <div class="button-group">
                            <a href="" class="button active"><span class="iconify" data-icon="ic:outline-timer"></span> Aguard. aprovação</a>
                            <a href="" class="button"><span class="iconify" data-icon="material-symbols:check-box-outline"></span> Aprovado</a>
                            <a href="" class="button"><span class="iconify" data-icon="fluent:dismiss-square-20-regular"></span>Reprovado</a>
                        </div>	
                        <div class="colunas2" style="grid-template-columns: 70% 1fr;">
                            <dl>
                                <dt>Título</dt>
                                <dd><input type="text" name="" value="10.000,00"></dd>
                            </dl>
                            <dl>
                                <dt>Data do pedido</dt>
                                <dd><input type="text" name="" value="25/06/2023"></dd>
                            </dl>
                        </div>
                </fieldset>	
                <fieldset>
                    <legend>Produtos</legend>	
                        <div class="box" style="padding: 0;">
                            <div>
                                <table class="list1" style="margin: 0;width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td class="list1__foto"><img src="img/ilustra-usuario.jpg" width="54" height="54"></td>
                                            <td>
                                                <h1>Produto 01 - Cor A</h1>
                                                <p>Marca X - 3 Gramas</p>
                                            </td>
                                            <td>
                                                <p>Menor preço: R$ 1,11</p>
                                                <p>Último preço: R$ 1,22</p>
                                            </td>
                                            <td><div style="border: 1px solid #B3B3B3;border-radius: 5px;width: 26px;padding: 2px 3px;"> <span class="iconify" data-icon="material-symbols:delete-outline-rounded" data-width="18"></span></div></td>
                                        </tr>						
                                    </tbody>
                                </table>
                                <section>
                                    <div class="list2">
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>Fornecedor</th>
                                                    <th>Menor preço</th>
                                                    <th>Último preço</th>
                                                    <th>Preço orçado</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <dl>
                                                            <dd><input style="background: #fff;" type="text" name="" value="Dental cremer" placeholder=""></dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd>R$ 1,33</dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd>R$ 1,33</dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd><input style="background: #fff;max-width: 70px;" type="text" name="" value="R$ 1,33" placeholder=""></dd>
                                                        </dl>
                                                    </td>
                                                    <td><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;display:block;margin: auto;" data-width="24"></span></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <dl>
                                                            <dd><input style="background: #fff;" type="text" name="" value="Dental cremer" placeholder=""></dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd>R$ 1,33</dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd>R$ 1,33</dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd><input style="background: #fff;max-width: 70px;" type="text" name="" value="R$ 1,33" placeholder=""></dd>
                                                        </dl>
                                                    </td>
                                                    <td><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;display:block;margin: auto;" data-width="24"></span></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <dl>
                                                            <dd><input style="background: #fff;" type="text" name="" value="Dental cremer" placeholder=""></dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd>R$ 1,33</dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd>R$ 1,33</dd>
                                                        </dl>
                                                    </td>
                                                    <td>
                                                        <dl>
                                                            <dd><input style="background: #fff;max-width: 70px;" type="text" name="" value="R$ 1,33" placeholder=""></dd>
                                                        </dl>
                                                    </td>
                                                    <td><span class="iconify" data-icon="material-symbols:check-box" style="color: #15b64f;display:block;margin: auto;" data-width="24"></span></td>
                                                </tr>
                                            </tbody>							
                                        </table>
                                    </div>
                                </section>
                            </div>
                        </div>
                </fieldset>	
            </form>
        </section>
    
    <script>

        $(".js-iside-compras").css("cursor", "pointer");

        $(".js-iside-compras").click(() => {
    
            $("#iside-estoque").fadeIn(100, function() {
                $("#iside-estoque .aside__inner9").addClass("active");
            });
        });
    </script>
    
    <?php
    }