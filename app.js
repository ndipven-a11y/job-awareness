(function(){
  const API_ROOT = 'api';
  let lastTimestamp = null; // ISO string

  async function fetchJobs(since){
    let url = `${API_ROOT}/jobs.php`;
    if(since) url += `?since=${encodeURIComponent(since)}`;
    const res = await fetch(url);
    if(!res.ok) throw new Error('Failed to fetch jobs');
    return res.json();
  }

  function renderJobs(jobs, prepend=false){
    const ul = document.getElementById('jobList');
    jobs.forEach(job=>{
      const li = document.createElement('li');
      li.className = 'job';
      li.innerHTML = `<h3>${escapeHtml(job.title)} <small>— ${escapeHtml(job.company)}</small></h3>
                      <div><strong>${escapeHtml(job.location || '')}</strong></div>
                      <p>${escapeHtml(job.description || '')}</p>
                      <small>${escapeHtml(job.created_at)}</small>`;
      if(prepend) ul.insertBefore(li, ul.firstChild); else ul.appendChild(li);
    });
  }

  function escapeHtml(s){
    if(!s) return '';
    return s.replace(/[&<>\"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":"&#39;"}[c]));
  }

  async function loadInitial(){
    try{
      const data = await fetchJobs();
      if(Array.isArray(data)){
        renderJobs(data);
        if(data.length) lastTimestamp = data[data.length-1].created_at; // oldest at end if API returns asc
        // choose latest time
        const latest = data.reduce((a,b)=> a.created_at > b.created_at ? a : b, data[0] || null);
        if(latest) lastTimestamp = latest.created_at;
      }
    }catch(e){
      showAlert('Could not load jobs: '+e.message);
    }
  }

  async function poll(){
    try{
      const data = await fetchJobs(lastTimestamp);
      if(Array.isArray(data) && data.length){
        // new jobs returned
        renderJobs(data, true);
        notifyNewJobs(data);
        // update lastTimestamp to latest job created_at
        const latest = data.reduce((a,b)=> a.created_at > b.created_at ? a : b, data[0]);
        lastTimestamp = latest.created_at;
      }
    }catch(e){
      console.warn('Polling error', e);
    } finally{
      setTimeout(poll, 15000); // poll every 15s
    }
  }

  function showAlert(message){
    const area = document.getElementById('alerts');
    const div = document.createElement('div');
    div.className = 'alert';
    div.textContent = message;
    area.appendChild(div);
    setTimeout(()=> area.removeChild(div), 8000);
  }

  function notifyNewJobs(jobs){
    jobs.forEach(job=>{
      const title = `New job: ${job.title} — ${job.company}`;
      const body = job.location ? `${job.location}` : '';
      if('Notification' in window){
        if(Notification.permission === 'granted'){
          new Notification(title, {body});
        } else if(Notification.permission !== 'denied'){
          Notification.requestPermission().then(p=>{
            if(p === 'granted') new Notification(title, {body});
          });
        }
      } else {
        showAlert(title);
      }
    });
  }

  async function submitAddJob(evt){
    evt.preventDefault();
    const f = evt.target;
    const data = {
      title: f.title.value.trim(),
      company: f.company.value.trim(),
      location: f.location.value.trim(),
      description: f.description.value.trim()
    };
    try{
      const res = await fetch(`${API_ROOT}/jobs.php`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
      const j = await res.json();
      if(res.ok){
        showAlert('Job added');
        // prepend new job locally
        renderJobs([j], true);
        lastTimestamp = j.created_at;
        f.reset();
      } else {
        showAlert(j.error || 'Failed to add job');
      }
    }catch(e){ showAlert('Error: '+e.message); }
  }

  async function submitSubscribe(evt){
    evt.preventDefault();
    const f = evt.target;
    const data = {email: f.email.value.trim(), keywords: f.keywords.value.trim()};
    try{
      const res = await fetch(`${API_ROOT}/subscribe.php`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
      const j = await res.json();
      if(res.ok) showAlert('Subscribed (demo)'); else showAlert(j.error || 'Failed');
      f.reset();
    }catch(e){ showAlert('Error: '+e.message); }
  }

  // wire up forms
  document.addEventListener('DOMContentLoaded', ()=>{
    document.getElementById('addJobForm').addEventListener('submit', submitAddJob);
    document.getElementById('subscribeForm').addEventListener('submit', submitSubscribe);
    loadInitial().then(()=> setTimeout(poll,15000));
  });

})();
