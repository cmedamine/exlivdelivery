CREATE TABLE IF NOT EXISTS auth_rate_limits (
    rate_key VARCHAR(80) NOT NULL,
    window_started INT NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    blocked_until INT NOT NULL DEFAULT 0,
    updated_at INT NOT NULL,
    PRIMARY KEY (rate_key),
    KEY auth_rate_limits_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
