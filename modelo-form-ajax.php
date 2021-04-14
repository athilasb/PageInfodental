
		<section class="content">

			<header class="caminho">
				<h1 class="caminho__titulo">Cadastros <i class="iconify" data-icon="bx-bx-chevron-right"></i> Pacientes <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Adicionar</strong></h1>
			</header>

			<section class="content-grid">

				<section class="content__item">

					<div class="acoes">
						<a href="modelo-registros.php" class="button button__lg button__ter"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						<a href="" class="button button__lg"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
					</div>

					<form method="post" class="form formulario-validacao">

						<fieldset>
							<legend>Dados da Unidade</legend>

							<dl>
								<dt>Título <span class="tooltip" title="Aqui vem a explicação do item"><i class="iconify" data-icon="bx-bx-info-circle"></i></span></dt>
								<dd><input type="text" name="titulo" value="" class="obg" /></dd>
							</dl>

							<dl>
								<dt>Tipo <span class="tooltip" title="Informe uma ou mais opções do que a loja fornece"><i class="iconify" data-icon="bx-bx-info-circle"></i></span></dt>
								<dd>
									<select name="unidade_tipo[]" class="chosen" multiple>
										<option value="loja">LOJA</option><option value="producao">PRODUÇÃO</option><option value="delivery">DELIVERY</option><option value="prestadora">PRESTADORA DE SERVIÇO</option>						</select>
								</dd>
							</dl>
							
							<div class="colunas4">
								<dl>
									<dt>CNPJ</dt>
									<dd><input type="text" name="cnpj" value="" class="cnpj" /></dd>
								</dl>
								<dl>
									<dt>Nome Fantasia</dt>
									<dd><input type="text" name="nome_fantasia" value="" class="" /></dd>
								</dl>
								<dl>
									<dt>Razão Social</dt>
									<dd><input type="text" name="razao_social" value="" class="" /></dd>
								</dl>
								<dl>
									<dt>Inscrição Estadual</dt>
									<dd><input type="text" name="inscricao_estadual" value="" class="" /></dd>
								</dl>
							</div>
						</fieldset>

						<fieldset>
							<legend>Dados de Endereço</legend>
							<div class="colunas4">
								<dl>
									<dt>CEP</dt>
									<dd><input type="text" name="cep" value="" class="" /></dd>
								</dl>
								<dl>
									<dt>&nbsp;</dt>
									<dd><a href="javascript:;" class="button button__sec js-cep"><i class="iconify" data-icon="bx-bx-search"></i></a></dd>
								</dl>
							</div>
							<div class="colunas4">
								<dl>
									<dt>Logradouro</dt>
									<dd><input type="text" name="logradouro" value="" class="" /></dd>
								</dl>
								<dl>
									<dt>Número</dt>
									<dd><input type="text" name="numero" value="" class="" /></dd>
								</dl>
								<dl class="dl2">
									<dt>Complemento</dt>
									<dd><input type="text" name="complemento" value="" class="" /></dd>
								</dl>
							</div>
							<div class="colunas4">
								<dl>
									<dt>Bairro</dt>
									<dd><input type="text" name="bairro" value="" class="" /></dd>
								</dl>
								<dl>
									<dt>Estado</dt>
									<dd>
										<select name="estado" class="js-estado">
											<option value="">-</option>
											<option value="AC">Acre</option><option value="AL">Alagoas</option><option value="AP">Amapá</option><option value="AM">Amazonas</option><option value="BA">Bahia</option><option value="CE">Ceará</option><option value="DF">Distrito Federal</option><option value="ES">Espírito Santo</option><option value="GO">Goiás</option><option value="MA">Maranhão</option><option value="MT">Mato Grosso</option><option value="MS">Mato Grosso do Sul</option><option value="MG">Minas Gerais</option><option value="PA">Pará</option><option value="PB">Paraíba</option><option value="PR">Paraná</option><option value="PE">Pernambuco</option><option value="PI">Piauí</option><option value="RJ">Rio de Janeiro</option><option value="RN">Rio Grande do Norte</option><option value="RS">Rio Grande do Sul</option><option value="RO">Rondônia</option><option value="RR">Roraima</option><option value="SC">Santa Catarina</option><option value="SP">São Paulo</option><option value="SE">Sergipe</option><option value="TO">Tocantins</option>							</select>
									</dd>
								</dl>
								<dl>
									<dt>Cidade</dt>
									<dd>
										<select name="cidade" class="js-cidade">
											<option value="">-</option>
										</select>
									</dd>
								</dl>
							</div>
						</fieldset>

					</form>

				</section>

			</section>
			
		</section>
