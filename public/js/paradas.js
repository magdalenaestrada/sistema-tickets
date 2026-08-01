


document.addEventListener('DOMContentLoaded',function(){

const CSRF=document
.querySelector('meta[name="csrf-token"]')
.getAttribute('content');

const tbody=document.getElementById('tbodyPueblitos');

const alerta=document.getElementById('alertaPueblitos');

const sucursales=@json($sucursales);

const distritos=@json($distritos);
    function mostrarAlerta(mensaje,tipo){

        alerta.innerHTML=`
        <div class="alert alert-${tipo} alert-dismissible fade show">

            ${mensaje}

            <button class="btn-close" data-bs-dismiss="alert"></button>

        </div>`;
    }

    function limpiarFormulario(){

        document.getElementById('sucursal_id').value='';
        document.getElementById('distrito_id').value='';
        document.getElementById('descripcion').value='';

    }

    function agregarFila(data){

        const vacia=document.getElementById('filaVacia');

        if(vacia) vacia.remove();

        const fila=document.createElement('tr');

        fila.id='fila-'+data.id;

        fila.dataset.id=data.id;
        fila.dataset.sucursal=data.sucursal_id;
        fila.dataset.distrito=data.distrito_id;
        fila.dataset.descripcion=data.descripcion;

        fila.innerHTML=`

        <td class="celdaSucursal">
            ${data.sucursal}
        </td>

        <td class="celdaDistrito">
            ${data.distrito}
        </td>

        <td class="celdaDescripcion">
            ${data.descripcion}
        </td>

        <td class="celdaAcciones">

            <button
                class="btn btn-sm btn-outline-primary btnEditar"
                data-id="${data.id}">

                Editar

            </button>

            <button
                class="btn btn-sm btn-outline-danger btnEliminar"
                data-id="${data.id}">

                Eliminar

            </button>

        </td>

        `;

        tbody.prepend(fila);

    }

    document
    .getElementById('btnGuardar')
    .addEventListener('click',function(){

        const sucursal_id=document.getElementById('sucursal_id').value;

        const distrito_id=document.getElementById('distrito_id').value;

        const descripcion=document.getElementById('descripcion').value.trim();

        if(!sucursal_id || !distrito_id || !descripcion){

            mostrarAlerta('Complete todos los campos','warning');

            return;

        }

        this.disabled=true;

        this.innerText='Guardando...';

        fetch("{{ route('pueblitos.store') }}",{

            method:'POST',

            headers:{
                'Content-Type':'application/json',
                'Accept':'application/json',
                'X-CSRF-TOKEN':CSRF
            },

            body:JSON.stringify({

                sucursal_id,
                distrito_id,
                descripcion

            })

        })

        .then(async r=>{

            const j=await r.json();

            if(!r.ok) throw j;

            return j;

        })

        .then(j=>{

            agregarFila(j.data);

            limpiarFormulario();

            mostrarAlerta(j.message,'success');

        })

        .catch(e=>{

            let m='Error al guardar';

            if(e.errors){

                m=Object.values(e.errors).flat().join('<br>');

            }

            mostrarAlerta(m,'danger');

        })

        .finally(()=>{

            this.disabled=false;

            this.innerText='Guardar';

        });

    });

    tbody.addEventListener('click',function(e){

        const btn=e.target.closest('.btnEliminar');

        if(!btn) return;

        if(!confirm('¿Eliminar este pueblito?')) return;

        const id=btn.dataset.id;

        fetch("{{ url('pueblitos') }}/"+id,{

            method:'DELETE',

            headers:{

                'X-CSRF-TOKEN':CSRF,
                'Accept':'application/json'

            }

        })

        .then(r=>r.json())

        .then(j=>{

            document.getElementById('fila-'+id).remove();

            mostrarAlerta(j.message,'success');

        })

        .catch(()=>{

            mostrarAlerta('No se pudo eliminar','danger');

        });

    });    // ============================
    // EDICIÓN INLINE
    // ============================

    function construirSelect(id, opciones, seleccionado, campo){

        let html=`<select id="${id}" class="form-control form-control-sm">`;

        html+=`<option value="">Seleccione</option>`;

        opciones.forEach(op=>{

            const texto=campo=='sucursal'
                ? op.nombre_comercial
                : op.descripcion;

            html+=`
                <option
                    value="${op.id}"
                    ${String(op.id)==String(seleccionado)?'selected':''}>
                    ${texto}
                </option>
            `;

        });

        html+=`</select>`;

        return html;

    }

    function entrarEdicion(fila){

        const id=fila.dataset.id;

        fila.querySelector('.celdaSucursal').innerHTML=

            construirSelect(
                'editSucursal'+id,
                sucursales,
                fila.dataset.sucursal,
                'sucursal'
            );

        fila.querySelector('.celdaDistrito').innerHTML=

            construirSelect(
                'editDistrito'+id,
                distritos,
                fila.dataset.distrito,
                'distrito'
            );

        fila.querySelector('.celdaDescripcion').innerHTML=`

            <input
                id="editDescripcion${id}"
                class="form-control form-control-sm"
                value="${fila.dataset.descripcion}">

        `;

        fila.querySelector('.celdaAcciones').innerHTML=`

            <button
                class="btn btn-success btn-sm btnGuardarEdicion"
                data-id="${id}">

                Guardar

            </button>

            <button
                class="btn btn-secondary btn-sm btnCancelar"
                data-id="${id}">

                Cancelar

            </button>

        `;

    }

    function salirEdicion(fila,data){

        fila.dataset.sucursal=data.sucursal_id;

        fila.dataset.distrito=data.distrito_id;

        fila.dataset.descripcion=data.descripcion;

        fila.querySelector('.celdaSucursal').innerHTML=data.sucursal;

        fila.querySelector('.celdaDistrito').innerHTML=data.distrito;

        fila.querySelector('.celdaDescripcion').innerHTML=data.descripcion;

        fila.querySelector('.celdaAcciones').innerHTML=`

            <button
                class="btn btn-outline-primary btn-sm btnEditar"
                data-id="${data.id}">

                Editar

            </button>

            <button
                class="btn btn-outline-danger btn-sm btnEliminar"
                data-id="${data.id}">

                Eliminar

            </button>

        `;

    }

    tbody.addEventListener('click',function(e){

        const editar=e.target.closest('.btnEditar');

        if(editar){

            entrarEdicion(
                document.getElementById('fila-'+editar.dataset.id)
            );

            return;

        }

        const cancelar=e.target.closest('.btnCancelar');

        if(cancelar){

            const fila=document.getElementById('fila-'+cancelar.dataset.id);

            salirEdicion(fila,{

                id:fila.dataset.id,

                sucursal_id:fila.dataset.sucursal,

                distrito_id:fila.dataset.distrito,

                descripcion:fila.dataset.descripcion,

                sucursal:sucursales.find(
                    x=>String(x.id)==fila.dataset.sucursal
                ).nombre_comercial,

                distrito:distritos.find(
                    x=>String(x.id)==fila.dataset.distrito
                ).descripcion

            });

            return;

        }

        const guardar=e.target.closest('.btnGuardarEdicion');

        if(!guardar) return;

        const id=guardar.dataset.id;

        const fila=document.getElementById('fila-'+id);

        const sucursal_id=document.getElementById('editSucursal'+id).value;

        const distrito_id=document.getElementById('editDistrito'+id).value;

        const descripcion=document
            .getElementById('editDescripcion'+id)
            .value
            .trim();

        if(!sucursal_id || !distrito_id || !descripcion){

            mostrarAlerta('Complete todos los campos','warning');

            return;

        }

        guardar.disabled=true;

        guardar.innerText='Guardando...';

        fetch("{{ url('pueblitos') }}/"+id,{

            method:'PUT',

            headers:{

                'Content-Type':'application/json',

                'Accept':'application/json',

                'X-CSRF-TOKEN':CSRF

            },

            body:JSON.stringify({

                sucursal_id,

                distrito_id,

                descripcion

            })

        })

        .then(async r=>{

            const j=await r.json();

            if(!r.ok) throw j;

            return j;

        })

        .then(j=>{

            salirEdicion(fila,j.data);

            mostrarAlerta(j.message,'success');

        })

        .catch(e=>{

            let m='No se pudo actualizar';

            if(e.errors){

                m=Object.values(e.errors)
                    .flat()
                    .join('<br>');

            }

            mostrarAlerta(m,'danger');

            guardar.disabled=false;

            guardar.innerText='Guardar';

        });

    });

});
