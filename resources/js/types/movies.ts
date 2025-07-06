export interface Movie {
  id: number;
  title: string;
  original_title?: string;
  overview: string;
  poster_path: string | null;
  backdrop_path: string | null;
  release_date: string;
  vote_average: number;
  vote_count: number;
  adult: boolean;
  original_language: string;
  popularity: number;
  genre_ids: number[];
  genres?: Genre[];
  homepage?: string;
  runtime?: number;
  status?: string;
  tagline?: string;
  poster_url?: string | null;
  backdrop_url?: string | null;
}

export interface Genre {
  id: number;
  name: string;
}

export interface PaginationInfo {
  current_page: number;
  total_pages: number;
  total_results: number;
  per_page: number;
  has_next_page: boolean;
  has_previous_page: boolean;
}

export interface ApiResponse<T> {
  success: boolean;
  data: T[];
  pagination?: PaginationInfo;
  error?: string;
}

export interface TrendingResponse extends ApiResponse<Movie> {
  time_window: 'day' | 'week';
}

export interface GenresResponse {
  success: boolean;
  genres: Genre[];
  error?: string;
}

export interface MoviesState {
  trending: Movie[];
  popular: Movie[];
  genres: Genre[];
  loading: boolean;
  error: string | null;
  lastFetch: {
    trending: number | null;
    popular: number | null;
    genres: number | null;
  };
}

export const CACHE_DURATION = 5 * 60 * 1000;
