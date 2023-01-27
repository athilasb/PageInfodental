const horarioDisponivel = (id_agenda,obj) => {
	let agenda_data = obj.find('input[name=agenda_data]').val();
	let agenda_duracao = obj.find('select[name=agenda_duracao]').val();
	let id_cadeira = obj.find('select[name=id_cadeira]').val();
	let id_profissional = obj.find('select.js-select-profissionais').val();
	let agenda_horaObj = obj.find('select[name=agenda_hora]');


	agenda_horaObj.find('option').remove();
	agenda_horaObj.append('<option value="">Carregando...</option>');

	obj = { agenda_data, agenda_duracao, id_cadeira, id_profissional }

	if(agenda_data.length>0 && agenda_duracao.length>0 && id_profissional.length>0 && id_cadeira.length>0) {
		let data = `ajax=asRelacionamentoPacienteHorarios&agenda_data=${agenda_data}&agenda_duracao=${agenda_duracao}&id_profissional=${id_profissional}&id_cadeira=${id_cadeira}&id_agenda=${id_agenda}`;

		data = {
			'ajax':'asRelacionamentoPacienteHorarios',
			'agenda_data':agenda_data,
			'agenda_duracao':agenda_duracao,
			'id_profissional':id_profissional,
			'id_cadeira':id_cadeira,
			'id_agenda':id_agenda
		}
		
		$.ajax({
			type:"POST",
			url:baseURLApiAside,
			data:data,
			success:function(rtn) {
				agenda_horaObj.find('option').remove();
				if(rtn.success) {

					if(rtn.horariosDisponiveis.length>0) {
						agenda_horaObj.append(`<option value="">Selecione o horário</option>`)
						rtn.horariosDisponiveis.forEach(x=>{

							agenda_horaObj.append(`<option value="${x}">${x}</option>`)
						})
					} else {
						agenda_horaObj.append(`<option value="">Nenhum horário disponível</option>`)
					}
				} else if(rtn.error) {
					agenda_horaObj.append(`<option value="">${rtn.error}</option>`);
				}
			}
		});
	} else {
		agenda_horaObj.find('option').remove();
		agenda_horaObj.append(`<option value="">Complete os campos</option>`);
	}
}

$(function(){
	$('.js-salvar-loading').click(function(){

		if(!$(this).attr('data-loading') || $(this).attr('data-loading')==0) {
			$(this).html('<span class="iconify" data-icon="eos-icons:loading"></span>');
			$(this).attr('data-loading',1);
		}
	})
})