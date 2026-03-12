import { beforeEach, describe, expect, it, vi } from "vitest";

const mocked = vi.hoisted(() => ({
  authStore: {
    isLoggedIn: false,
    ensureAuthChecked: vi.fn(async () => {}),
    hasPermission: vi.fn(() => true),
  },
  appStore: {
    setLoading: vi.fn(),
    showError: vi.fn(),
    setBreadcrumbs: vi.fn(),
  },
}));

vi.mock("@/stores/auth", () => ({
  useAuthStore: () => mocked.authStore,
}));

vi.mock("@/stores/app", () => ({
  useAppStore: () => mocked.appStore,
}));

async function loadRouter() {
  vi.resetModules();

  const { default: router } = await import("@/router");
  return router;
}

describe("router smoke flows", () => {
  beforeEach(() => {
    mocked.authStore.isLoggedIn = false;
    mocked.authStore.ensureAuthChecked.mockClear();
    mocked.authStore.hasPermission.mockReset();
    mocked.authStore.hasPermission.mockReturnValue(true);
    mocked.appStore.setLoading.mockClear();
    mocked.appStore.showError.mockClear();
    mocked.appStore.setBreadcrumbs.mockClear();
  });

  it("redirects unauthenticated users to login", async () => {
    const router = await loadRouter();

    await router.push("/profile");
    await router.isReady();

    expect(mocked.authStore.ensureAuthChecked).toHaveBeenCalled();
    expect(router.currentRoute.value.name).toBe("Login");
    expect(router.currentRoute.value.query.redirect).toBe("/profile");
  });

  it("redirects authenticated users away from login", async () => {
    mocked.authStore.isLoggedIn = true;

    const router = await loadRouter();

    await router.push("/login");
    await router.isReady();

    expect(router.currentRoute.value.name).toBe("Profile");
  });

  it("blocks users without route permission and keeps previous page", async () => {
    mocked.authStore.isLoggedIn = true;

    const router = await loadRouter();

    await router.push("/profile");
    await router.isReady();

    mocked.authStore.hasPermission.mockReturnValue(false);

    await router.push("/report/dashboard");

    expect(mocked.appStore.showError).toHaveBeenCalledTimes(1);
    expect(router.currentRoute.value.fullPath).toBe("/profile");
  });
});
