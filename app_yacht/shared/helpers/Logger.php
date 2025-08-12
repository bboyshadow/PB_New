<?php

/**
 * Logger Helper for App Yacht
 * Provides simple logging functionality with levels
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Logger class for debugging and monitoring
 */
class Logger {

	/**
	 * Log levels
	 */
	const ERROR   = 'ERROR';
	const WARNING = 'WARNING';
	const INFO    = 'INFO';
	const DEBUG   = 'DEBUG';

	/**
	 * Log file directory
	 */
	const LOG_DIR = WP_CONTENT_DIR . '/app_yacht_logs/';

	/**
	 * Log an error message
	 *
	 * @param string $message The error message
	 * @param array  $context Additional context data
	 */
	public static function error( $message, $context = array() ) {
		self::log( self::ERROR, $message, $context );
	}

	/**
	 * Log a warning message
	 *
	 * @param string $message The warning message
	 * @param array  $context Additional context data
	 */
	public static function warning( $message, $context = array() ) {
		self::log( self::WARNING, $message, $context );
	}

	/**
	 * Log an info message
	 *
	 * @param string $message The info message
	 * @param array  $context Additional context data
	 */
	public static function info( $message, $context = array() ) {
		self::log( self::INFO, $message, $context );
	}

	/**
	 * Log a debug message
	 *
	 * @param string $message The debug message
	 * @param array  $context Additional context data
	 */
	public static function debug( $message, $context = array() ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			self::log( self::DEBUG, $message, $context );
		}
	}

	/**
	 * Write log entry
	 *
	 * @param string $level   Log level
	 * @param string $message Log message
	 * @param array  $context Additional context data
	 */
	private static function log( $level, $message, $context = array() ) {
		// Check feature flag and level
		if ( ! class_exists( 'AppYachtConfig' ) ) {
			return; // Avoid fatal if config not loaded
		}
		$config   = AppYachtConfig::get();
		$features = $config['features'] ?? array();
		$logging  = $config['logging'] ?? array();

		if ( empty( $features['enhanced_logging'] ) ) {
			return; // logging disabled via feature flag
		}

		$level_order = array(
			self::ERROR   => 0,
			self::WARNING => 1,
			self::INFO    => 2,
			self::DEBUG   => 3,
		);
		$current_level = strtoupper( $logging['level'] ?? 'ERROR' );
		$current_idx   = $level_order[ $current_level ] ?? 0;
		$incoming_idx  = $level_order[ $level ] ?? 0;
		if ( $incoming_idx > $current_idx ) {
			return; // skip less important logs
		}

		// Ensure log directory exists
		if ( ! file_exists( self::LOG_DIR ) ) {
			wp_mkdir_p( self::LOG_DIR );
		}

		// Create log entry
		$timestamp = current_time( 'Y-m-d H:i:s' );
		$entry     = sprintf(
			"[%s] %s: %s",
			$timestamp,
			$level,
			$message
		);

		// Add context if provided (limit size)
		if ( ! empty( $context ) ) {
			$encoded = wp_json_encode( $context );
			if ( strlen( $encoded ) > 2000 ) {
				$encoded = substr( $encoded, 0, 2000 ) . '... [truncated]';
			}
			$entry .= ' | Context: ' . $encoded;
		}

		$entry .= PHP_EOL;

		// Determine log file
		$log_file = self::LOG_DIR . 'app_yacht_' . current_time( 'Y-m-d' ) . '.log';

		// Rotate if too large
		$max_size = intval( $logging['max_file_size'] ?? ( 5 * 1024 * 1024 ) );
		if ( file_exists( $log_file ) && filesize( $log_file ) > $max_size ) {
			$timestamp_short = current_time( 'His' );
			@rename( $log_file, self::LOG_DIR . 'app_yacht_' . current_time( 'Y-m-d' ) . '_' . $timestamp_short . '.log' );
		}

		// Write to file
		error_log( $entry, 3, $log_file );

		// Also log to WordPress debug.log if WP_DEBUG_LOG is enabled
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( '[APP_YACHT] ' . $entry );
		}
	}

	/**
	 * Get recent log entries
	 *
	 * @param int    $lines Number of lines to retrieve
	 * @param string $date  Date in Y-m-d format (default: today)
	 * @return array Log entries
	 */
	public static function getRecentLogs( $lines = 50, $date = null ) {
		// unchanged
		if ( ! $date ) {
			$date = current_time( 'Y-m-d' );
		}
		$log_file = self::LOG_DIR . 'app_yacht_' . $date . '.log';
		if ( ! file_exists( $log_file ) ) {
			return array();
		}
		$file_lines = file( $log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		return array_slice( $file_lines, -$lines );
	}

	/**
	 * Clear old log files (older than specified days)
	 *
	 * @param int $days Days to keep (default: 7)
	 */
	public static function clearOldLogs( $days = 7 ) {
		if ( ! file_exists( self::LOG_DIR ) ) {
			return;
		}
		$files  = glob( self::LOG_DIR . 'app_yacht_*.log' );
		$cutoff = time() - ( $days * 24 * 60 * 60 );
		foreach ( $files as $file ) {
			if ( filemtime( $file ) < $cutoff ) {
				@unlink( $file );
			}
		}
	}
}