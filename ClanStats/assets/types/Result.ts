export type Result<T> = {
  success: boolean;
  data: T | null;
  message?: string;
};
