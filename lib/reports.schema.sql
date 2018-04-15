DROP TABLE IF EXISTS dmck_audio_log_reports;
create table dmck_audio_log_reports (
  id serial primary key,
  data json,
  updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci;