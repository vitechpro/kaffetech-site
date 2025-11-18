<?php
// painel/tv_tokens.php
require_once __DIR__ . '/../inc/bootstrap.php';
// aqui você pode checar login/perfil se já tiver middleware
// ex: require_admin();

$baseUrl = rtrim((string) (CONFIG['base_url'] ?? ''), '/');
if ($baseUrl === '') {
    // fallback simples, ajusta se tiver constante própria
    $proto   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $proto . $host;
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <title>Tokens de TV - Painel</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin:0; background:#f5f5f5; }
    header { padding:16px 24px; background:#202329; color:#fff; display:flex; justify-content:space-between; align-items:center; }
    main { padding:24px; max-width:1000px; margin:0 auto; }
    h1 { margin:0 0 8px; font-size:22px; }
    .card { background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 1px 3px rgba(0,0,0,.08); }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th, td { padding:8px 6px; border-bottom:1px solid #eee; text-align:left; font-size:14px; }
    th { font-weight:600; background:#fafafa; }
    .pill { display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; }
    .pill-on { background:#e6ffed; color:#067d1f; border:1px solid #9ae6b4; }
    .pill-off { background:#ffecec; color:#a51919; border:1px solid #feb2b2; }
    .btn { padding:6px 10px; border-radius:8px; border:1px solid #ccc; background:#fff; cursor:pointer; font-size:13px; }
    .btn-primary { background:#2563eb; border-color:#2563eb; color:#fff; }
    .btn-danger { background:#dc2626; border-color:#dc2626; color:#fff; }
    .btn-sm { font-size:12px; padding:4px 8px; }
    .row-actions { display:flex; gap:4px; }
    .top-bar { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:10px; }
    input[type="text"] { padding:6px 8px; border-radius:8px; border:1px solid #d4d4d4; min-width:220px; }
    .muted { color:#666; font-size:13px; }
    @media (max-width:700px) {
      table { font-size:12px; }
      th:nth-child(3), td:nth-child(3) { display:none; } /* esconde token em telas muito pequenas */
    }
  </style>
</head>
<body>
<header>
  <div>
    <div style="font-size:14px;opacity:.8;">Painel</div>
    <div style="font-size:18px;font-weight:600;">Tokens de TV</div>
  </div>
</header>

<main>
  <section class="card">
    <div class="top-bar">
      <div>
        <strong>Gerenciar acessos ao painel de TV</strong>
        <div class="muted">Crie tokens exclusivos para cada TV (recepção, sala de reunião, etc.).</div>
      </div>
      <div>
        <input type="text" id="nova_desc" placeholder="Descrição do novo token (ex: TV Recepção)" />
        <button class="btn btn-primary" id="btn_criar">Criar token</button>
      </div>
    </div>

    <table id="tab_tokens">
      <thead>
        <tr>
          <th>ID</th>
          <th>Descrição</th>
          <th>Token</th>
          <th>Ativo</th>
          <th>Criado em</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr><td colspan="6" class="muted">Carregando...</td></tr>
      </tbody>
    </table>
  </section>
</main>

<script>
(function(){
  const BASE_URL = <?=json_encode($baseUrl, JSON_UNESCAPED_SLASHES)?>;
  const tbody = document.querySelector('#tab_tokens tbody');
  const novaDesc = document.getElementById('nova_desc');
  const btnCriar = document.getElementById('btn_criar');

  async function j(url, opts){
    const r = await fetch(url, opts);
    return r.json();
  }

  function linkTv(token){
    return BASE_URL + '/public/painel_tv.html?token=' + encodeURIComponent(token);
  }

  function linha(row){
    const tr = document.createElement('tr');

    const tdId = document.createElement('td');
    tdId.textContent = row.id;

    const tdDesc = document.createElement('td');
    const inp = document.createElement('input');
    inp.type = 'text';
    inp.value = row.descricao || '';
    inp.style.width = '100%';
    tdDesc.appendChild(inp);

    const tdToken = document.createElement('td');
    const small = document.createElement('div');
    small.textContent = row.token;
    small.style.fontFamily = 'monospace';
    small.style.fontSize = '12px';
    const link = document.createElement('div');
    link.className = 'muted';
    link.textContent = 'Copiar link';
    link.style.cursor = 'pointer';
    link.onclick = () => {
      navigator.clipboard.writeText(linkTv(row.token));
      alert('Link copiado para área de transferência');
    };
    tdToken.appendChild(small);
    tdToken.appendChild(link);

    const tdAtivo = document.createElement('td');
    const span = document.createElement('span');
    span.className = 'pill ' + (row.ativo === '1' || row.ativo === 1 ? 'pill-on' : 'pill-off');
    span.textContent = (row.ativo === '1' || row.ativo === 1) ? 'Ativo' : 'Inativo';
    tdAtivo.appendChild(span);

    const tdCriado = document.createElement('td');
    tdCriado.textContent = row.criado_em || '';

    const tdAcoes = document.createElement('td');
    const wrap = document.createElement('div');
    wrap.className = 'row-actions';

    const btnSalvar = document.createElement('button');
    btnSalvar.className = 'btn btn-sm';
    btnSalvar.textContent = 'Salvar';
    btnSalvar.onclick = async () => {
      const fd = new FormData();
      fd.append('id', row.id);
      fd.append('descricao', inp.value.trim());
      fd.append('ativo', (row.ativo === '1' || row.ativo === 1) ? 1 : 0);
      const js = await j('/api/tv_tokens_salvar.php', {method:'POST', body:fd});
      if (!js.ok) { alert(js.erro || 'Erro ao salvar'); return; }
      carregar();
    };

    const btnToggle = document.createElement('button');
    btnToggle.className = 'btn btn-sm';
    btnToggle.textContent = (row.ativo === '1' || row.ativo === 1) ? 'Desativar' : 'Ativar';
    btnToggle.onclick = async () => {
      const fd = new FormData();
      fd.append('id', row.id);
      fd.append('descricao', inp.value.trim());
      fd.append('ativo', (row.ativo === '1' || row.ativo === 1) ? 0 : 1);
      const js = await j('/api/tv_tokens_salvar.php', {method:'POST', body:fd});
      if (!js.ok) { alert(js.erro || 'Erro ao salvar'); return; }
      carregar();
    };

    const btnRegen = document.createElement('button');
    btnRegen.className = 'btn btn-sm';
    btnRegen.textContent = 'Novo token';
    btnRegen.onclick = async () => {
      if (!confirm('Gerar um novo token para esta TV? O link antigo deixará de funcionar.')) return;
      const fd = new FormData();
      fd.append('id', row.id);
      fd.append('descricao', inp.value.trim());
      fd.append('ativo', (row.ativo === '1' || row.ativo === 1) ? 1 : 0);
      fd.append('regen', 1);
      const js = await j('/api/tv_tokens_salvar.php', {method:'POST', body:fd});
      if (!js.ok) { alert(js.erro || 'Erro ao salvar'); return; }
      carregar();
    };

    const btnDel = document.createElement('button');
    btnDel.className = 'btn btn-sm btn-danger';
    btnDel.textContent = 'Excluir';
    btnDel.onclick = async () => {
      if (!confirm('Tem certeza que deseja excluir este token?')) return;
      const fd = new FormData();
      fd.append('id', row.id);
      const js = await j('/api/tv_tokens_excluir.php', {method:'POST', body:fd});
      if (!js.ok) { alert(js.erro || 'Erro ao excluir'); return; }
      carregar();
    };

    wrap.appendChild(btnSalvar);
    wrap.appendChild(btnToggle);
    wrap.appendChild(btnRegen);
    wrap.appendChild(btnDel);
    tdAcoes.appendChild(wrap);

    tr.appendChild(tdId);
    tr.appendChild(tdDesc);
    tr.appendChild(tdToken);
    tr.appendChild(tdAtivo);
    tr.appendChild(tdCriado);
    tr.appendChild(tdAcoes);

    return tr;
  }

  async function carregar(){
    tbody.innerHTML = '<tr><td colspan="6" class="muted">Carregando...</td></tr>';
    const js = await j('/api/tv_tokens_listar.php');
    tbody.innerHTML = '';
    if (!js.ok) {
      tbody.innerHTML = '<tr><td colspan="6" class="muted">Erro ao carregar tokens</td></tr>';
      return;
    }
    if (!js.data || js.data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="muted">Nenhum token cadastrado ainda.</td></tr>';
      return;
    }
    js.data.forEach(row => {
      tbody.appendChild(linha(row));
    });
  }

  btnCriar.onclick = async () => {
    const desc = novaDesc.value.trim();
    if (!desc) {
      alert('Informe uma descrição para o token (ex: TV Recepção).');
      return;
    }
    const fd = new FormData();
    fd.append('descricao', desc);
    fd.append('ativo', 1);
    const js = await j('/api/tv_tokens_salvar.php', {method:'POST', body:fd});
    if (!js.ok) {
      alert(js.erro || 'Erro ao criar token');
      return;
    }
    novaDesc.value = '';
    carregar();
  };

  carregar();
})();
</script>
</body>
</html>
