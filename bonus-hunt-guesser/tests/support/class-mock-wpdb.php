<?php

class MockWPDB {
	public $prefix     = 'wp_';
	public $last_error = '';
	public $insert_id  = 0;

	public $bonus_hunts        = array();
	public $guesses            = array();
	public $hunt_winners       = array();
	public $tournament_results = array();
	public $tournaments_hunts  = array();
	public $tournaments        = array();
	public $translations       = array();
	public $usermeta           = 'wp_usermeta';
	public $usermeta_data      = array();
	public $tables             = array( 'wp_usermeta' => true );

	private $winner_auto_increment     = 0;
	private $tournament_auto_increment = 0;

	public function prepare( $query, ...$args ) {
		if ( 1 === count( $args ) && is_array( $args[0] ) ) {
			$args = $args[0];
		}

		$mapped = array_map(
			static function ( $value ) {
				if ( is_string( $value ) ) {
					return "'" . addslashes( $value ) . "'";
				}

				if ( null === $value ) {
					return 'NULL';
				}

				return $value;
			},
			$args
		);

		return vsprintf( $query, $mapped );
	}

	public function set_usermeta( $user_id, $key, $value ) {
		$user_id = (int) $user_id;
		if ( ! isset( $this->usermeta_data[ $user_id ] ) ) {
			$this->usermeta_data[ $user_id ] = array();
		}

		$this->usermeta_data[ $user_id ][ $key ] = maybe_serialize( $value );
	}

	public function delete_usermeta( $user_id, $key ) {
		$user_id = (int) $user_id;
		if ( isset( $this->usermeta_data[ $user_id ][ $key ] ) ) {
			unset( $this->usermeta_data[ $user_id ][ $key ] );
		}
	}

	public function set_table_exists( $table ) {
		$this->tables[ $table ] = true;
	}

	public function get_row( $query ) {
		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_bonus_hunts' ) ) {
			if ( preg_match( '/WHERE id = (\d+)/', $query, $matches ) ) {
				$id = (int) $matches[1];
				if ( isset( $this->bonus_hunts[ $id ] ) ) {
					return (object) $this->bonus_hunts[ $id ];
				}
			}
		}

		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_tournament_results' ) ) {
			if ( preg_match( '/tournament_id = (\d+)/', $query, $tournament_match ) && preg_match( '/user_id = (\d+)/', $query, $user_match ) ) {
				$tournament_id = (int) $tournament_match[1];
				$user_id       = (int) $user_match[1];

				foreach ( $this->tournament_results as $row ) {
					if ( (int) $row['tournament_id'] === $tournament_id && (int) $row['user_id'] === $user_id ) {
						return (object) $row;
					}
				}
			}
		}

		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_tournaments' ) ) {
			if ( preg_match( '/WHERE id = (\d+)/', $query, $matches ) ) {
				$id = (int) $matches[1];
				if ( isset( $this->tournaments[ $id ] ) ) {
					return (object) $this->tournaments[ $id ];
				}
			}
		}

		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_translations' ) ) {
			$slug   = $this->match_string( "/slug\s*=\s*'([^']+)'/", $query );
			$locale = $this->match_string( "/locale\s*=\s*'([^']+)'/", $query );

			foreach ( $this->translations as $row ) {
				if ( $row['slug'] === $slug && $row['locale'] === $locale ) {
					return (object) $row;
				}
			}
		}

		return null;
	}

	public function get_results( $query, $output = OBJECT ) {
		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_guesses' ) ) {
			$hunt_id = $this->match_int( '/WHERE hunt_id = (\d+)/', $query );
			$limit   = $this->match_int( '/LIMIT (\d+)/', $query );

			$final_balance = 0.0;
			if ( preg_match( '/\(\s*([0-9\.\-]+)\s*-\s*guess\s*\)\s+AS\s+diff/i', $query, $matches ) ) {
				$final_balance = (float) $matches[1];
			} elseif ( preg_match( '/ABS\(\s*guess\s*-\s*([0-9\.\-]+)\s*\)/i', $query, $matches ) ) {
				$final_balance = (float) $matches[1];
			}

			$filtered = array();
			foreach ( $this->guesses as $guess ) {
				if ( (int) $guess['hunt_id'] !== $hunt_id ) {
					continue;
				}

				$diff_signed = $final_balance - (float) $guess['guess'];
				$filtered[]  = (object) array(
					'user_id'  => (int) $guess['user_id'],
					'guess'    => (float) $guess['guess'],
					'diff'     => $diff_signed,
					'abs_diff' => abs( $diff_signed ),
					'id'       => (int) $guess['id'],
				);
			}

			usort(
				$filtered,
				static function ( $a, $b ) {
					if ( $a->abs_diff === $b->abs_diff ) {
						return $a->id <=> $b->id;
					}

					return ( $a->abs_diff < $b->abs_diff ) ? -1 : 1;
				}
			);

			if ( $limit > 0 ) {
				$filtered = array_slice( $filtered, 0, $limit );
			}

			foreach ( $filtered as $row ) {
				unset( $row->id, $row->abs_diff );
			}

			return $this->format_results_objects( $filtered, $output );
		}

		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_tournaments' ) ) {
			$ids = array();
			if ( preg_match( '/WHERE id IN \(([^\)]+)\)/', $query, $matches ) ) {
				$ids = array_map( 'intval', preg_split( '/,\s*/', $matches[1] ) );
			} elseif ( preg_match( '/WHERE id = (\d+)/', $query, $match ) ) {
				$ids = array( (int) $match[1] );
			}

			$results = array();
			foreach ( $ids as $id ) {
				if ( isset( $this->tournaments[ $id ] ) ) {
					$results[] = (object) $this->tournaments[ $id ];
				}
			}

			return $this->format_results( $results, $output );
		}

		if (
			false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_hunt_winners' )
			&& (
				false !== strpos( $query, 'bhg_tournaments' )
				|| false !== strpos( $query, 'bhg_tournaments_hunts' )
			)
		) {
			$tournament_id = $this->match_int( '/t\.id = (\d+)/', $query );
			if ( 0 === $tournament_id ) {
				$tournament_id = $this->match_int( '/WHERE t.id = (\d+)/', $query );
			}
			if ( 0 === $tournament_id ) {
				$tournament_id = $this->match_int( '/ht\.tournament_id = (\d+)/', $query );
			}
			if ( 0 === $tournament_id ) {
				$tournament_id = $this->match_int( '/h\.tournament_id = (\d+)/', $query );
			}

			$results = array();
			foreach ( $this->hunt_winners as $winner ) {
				$hunt_id = (int) $winner['hunt_id'];

				$assigned = array();
				foreach ( $this->tournaments_hunts as $map ) {
					if ( (int) $map['hunt_id'] === $hunt_id ) {
						$assigned[] = (int) $map['tournament_id'];
					}
				}

				if ( empty( $assigned ) && isset( $this->bonus_hunts[ $hunt_id ]['tournament_id'] ) ) {
					$assigned[] = (int) $this->bonus_hunts[ $hunt_id ]['tournament_id'];
				}

				if ( ! in_array( $tournament_id, $assigned, true ) ) {
					continue;
				}

				$tournament = $this->tournaments[ $tournament_id ] ?? array();
				$mode       = $tournament['participants_mode'] ?? 'winners';
				$hunt       = $this->bonus_hunts[ $hunt_id ] ?? array();
				$winners    = isset( $hunt['winners_count'] ) ? (int) $hunt['winners_count'] : 0;

				$event_date = $winner['created_at'] ?? null;
				if ( ! $event_date && isset( $hunt['closed_at'] ) ) {
					$event_date = $hunt['closed_at'];
				}
				if ( ! $event_date && isset( $hunt['updated_at'] ) ) {
					$event_date = $hunt['updated_at'];
				}
				if ( ! $event_date && isset( $hunt['created_at'] ) ) {
					$event_date = $hunt['created_at'];
				}
				if ( ! $event_date ) {
					$event_date = '2024-01-01 00:00:00';
				}

				$results[] = (object) array(
					'hw_id'             => isset( $winner['id'] ) ? (int) $winner['id'] : 0,
					'user_id'           => (int) $winner['user_id'],
					'position'          => (int) $winner['position'],
					'eligible'          => isset( $winner['eligible'] ) ? (int) $winner['eligible'] : 1,
					'participants_mode' => $mode,
					'winners_count'     => $winners,
					'event_date'        => $event_date,
				);
			}

			return $this->format_results( $results, $output );
		}

		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_hunt_winners' ) ) {
			$hunt_id = $this->match_int( '/WHERE hunt_id = (\d+)/', $query );

			$results = array();
			foreach ( $this->hunt_winners as $winner ) {
				if ( (int) $winner['hunt_id'] === $hunt_id ) {
					$results[] = array(
						'user_id'  => (int) $winner['user_id'],
						'position' => isset( $winner['position'] ) ? (int) $winner['position'] : 0,
					);
				}
			}

			return $this->format_results( $results, $output );
		}

		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'usermeta' ) ) {
			$key     = $this->match_string( "/meta_key\s*=\s*'([^']+)'/", $query );
			$results = array();

			foreach ( $this->usermeta_data as $user_id => $meta ) {
				if ( $key ) {
					if ( isset( $meta[ $key ] ) ) {
						$results[] = array(
							'user_id'    => (int) $user_id,
							'meta_value' => $meta[ $key ],
						);
					}
				} else {
					foreach ( $meta as $meta_key => $value ) {
						$results[] = array(
							'user_id'    => (int) $user_id,
							'meta_key'   => $meta_key,
							'meta_value' => $value,
						);
					}
				}
			}

			return $this->format_results( $results, $output );
		}

		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_translations' ) ) {
			$results = array();

			foreach ( $this->translations as $row ) {
				$results[] = array(
					'slug'         => $row['slug'],
					'default_text' => $row['default_text'],
					'text'         => $row['text'],
					'locale'       => $row['locale'],
				);
			}

			return $this->format_results( $results, $output );
		}

		return array();
	}

	public function get_col( $query ) {
		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_tournaments_hunts' ) ) {
			$hunt_id = $this->match_int( '/WHERE hunt_id = (\d+)/', $query );
			$results = array();

			foreach ( $this->tournaments_hunts as $row ) {
				if ( (int) $row['hunt_id'] === $hunt_id ) {
					$results[] = (int) $row['tournament_id'];
				}
			}

			return $results;
		}

		if ( false !== strpos( $query, 'FROM ' . $this->prefix . 'bhg_bonus_hunts' ) ) {
			$hunt_id = $this->match_int( '/WHERE id = (\d+)/', $query );
			if ( isset( $this->bonus_hunts[ $hunt_id ] ) && isset( $this->bonus_hunts[ $hunt_id ]['tournament_id'] ) ) {
				return array( (int) $this->bonus_hunts[ $hunt_id ]['tournament_id'] );
			}
		}

		return array();
	}

	public function update( $table, $data, $where, $format = null, $where_format = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( false !== strpos( $table, 'bhg_bonus_hunts' ) ) {
			if ( isset( $where['id'] ) ) {
				$id = (int) $where['id'];
				if ( isset( $this->bonus_hunts[ $id ] ) ) {
					$this->bonus_hunts[ $id ] = array_merge( $this->bonus_hunts[ $id ], $data );
					return 1;
				}
			}
			return false;
		}

		if ( false !== strpos( $table, 'bhg_tournament_results' ) ) {
			if ( isset( $where['id'] ) ) {
				$id = (int) $where['id'];
				foreach ( $this->tournament_results as &$row ) {
					if ( (int) $row['id'] === $id ) {
						$row = array_merge( $row, $data );
						return 1;
					}
				}
			}

			return false;
		}

		return false;
	}

	public function insert( $table, $data, $format = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( false !== strpos( $table, 'bhg_hunt_winners' ) ) {
			if ( ! isset( $data['eligible'] ) ) {
				$data['eligible'] = 1;
			}
			$data['id']           = ++$this->winner_auto_increment;
			$this->hunt_winners[] = $data;
			$this->insert_id      = $data['id'];
			return 1;
		}

		if ( false !== strpos( $table, 'bhg_tournament_results' ) ) {
			$data['id']                 = ++$this->tournament_auto_increment;
			$this->tournament_results[] = $data;
			$this->insert_id            = $data['id'];
			return 1;
		}

		if ( false !== strpos( $table, 'bhg_bonus_hunts' ) ) {
			$id                       = ++$this->winner_auto_increment;
			$data['id']               = $id;
			$this->bonus_hunts[ $id ] = $data;
			$this->insert_id          = $id;
			return 1;
		}

		if ( false !== strpos( $table, 'bhg_translations' ) ) {
			$this->translations[] = array(
				'slug'         => isset( $data['slug'] ) ? $data['slug'] : '',
				'default_text' => isset( $data['default_text'] ) ? $data['default_text'] : '',
				'text'         => isset( $data['text'] ) ? $data['text'] : '',
				'locale'       => isset( $data['locale'] ) ? $data['locale'] : '',
			);

			return 1;
		}

		return false;
	}

	public function delete( $table, $where, $where_format = null ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( false !== strpos( $table, 'bhg_hunt_winners' ) && isset( $where['hunt_id'] ) ) {
			$hunt_id = (int) $where['hunt_id'];
			$before  = count( $this->hunt_winners );

			$this->hunt_winners = array_values(
				array_filter(
					$this->hunt_winners,
					static function ( $winner ) use ( $hunt_id ) {
						return (int) $winner['hunt_id'] !== $hunt_id;
					}
				)
			);

			return $before - count( $this->hunt_winners );
		}

		if ( false !== strpos( $table, 'bhg_tournament_results' ) ) {
			if ( isset( $where['id'] ) ) {
				$id     = (int) $where['id'];
				$before = count( $this->tournament_results );

				$this->tournament_results = array_values(
					array_filter(
						$this->tournament_results,
						static function ( $row ) use ( $id ) {
							return (int) $row['id'] !== $id;
						}
					)
				);

				return $before - count( $this->tournament_results );
			}

			if ( isset( $where['tournament_id'] ) ) {
				$target = (int) $where['tournament_id'];
				$before = count( $this->tournament_results );

				$this->tournament_results = array_values(
					array_filter(
						$this->tournament_results,
						static function ( $row ) use ( $target ) {
							return (int) $row['tournament_id'] !== $target;
						}
					)
				);

				return $before - count( $this->tournament_results );
			}
		}

		return false;
	}

	public function get_var( $query ) {
		if ( false !== strpos( $query, 'SHOW TABLES LIKE' ) ) {
			$table = $this->match_string( "/LIKE '([^']+)'/", $query );

			return isset( $this->tables[ $table ] ) ? $table : null;
		}

		if ( false !== strpos( $query, 'SELECT COUNT(*) FROM ' . $this->prefix . 'bhg_translations' ) ) {
			$slug   = $this->match_string( "/slug\s*=\s*'([^']+)'/", $query );
			$locale = $this->match_string( "/locale\s*=\s*'([^']+)'/", $query );

			foreach ( $this->translations as $row ) {
				if ( $row['slug'] === $slug && $row['locale'] === $locale ) {
					return 1;
				}
			}

			return 0;
		}

		return null;
	}

	private function format_results( array $rows, $output ) {
		if ( ARRAY_A === $output || 'ARRAY_A' === $output ) {
			return $rows;
		}

		return array_map(
			static function ( $row ) {
				return (object) $row;
			},
			$rows
		);
	}

	private function format_results_objects( array $rows, $output ) {
		if ( ARRAY_A === $output || 'ARRAY_A' === $output ) {
			return array_map(
				static function ( $row ) {
					return (array) $row;
				},
				$rows
			);
		}

		return $rows;
	}

	private function match_int( $pattern, $subject ) {
		if ( preg_match( $pattern, $subject, $matches ) ) {
			return (int) $matches[1];
		}

		return 0;
	}

	private function match_string( $pattern, $subject ) {
		if ( preg_match( $pattern, $subject, $matches ) ) {
			return $matches[1];
		}

		return '';
	}

	private function match_float( $pattern, $subject ) {
		if ( preg_match( $pattern, $subject, $matches ) ) {
			return (float) $matches[1];
		}

		return 0.0;
	}
}
