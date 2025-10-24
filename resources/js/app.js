import 'bootstrap';
import { createIcons, icons } from 'lucide';
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css'; 

document.addEventListener('DOMContentLoaded', () => {
  // Lucide
  createIcons({ icons });

  // Flatpickr
  flatpickr(".fecha-input", {
    locale: 'es'
  });

  // DataTable
  const table = document.querySelector('#miTabla');
  if (table) {
    new DataTable(table);
  }

  // Fecha actual
  const dateEl = document.getElementById('dateDisplay');
  if (dateEl) {
    const now = new Date();
    const formattedDate = now.toLocaleDateString('es-ES', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
    dateEl.textContent = formattedDate;
  }
});
