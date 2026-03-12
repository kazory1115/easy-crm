import { afterEach, beforeEach, vi } from "vitest";

Object.defineProperty(window, "scrollTo", {
  value: vi.fn(),
  writable: true,
});

beforeEach(() => {
  localStorage.clear();
  sessionStorage.clear();
  document.title = "Easy CRM";
});

afterEach(() => {
  vi.clearAllMocks();
});
