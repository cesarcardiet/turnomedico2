import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../core/api/auth_repository.dart';
import '../core/network/api_client.dart';

/// Pantalla de autenticación: pestañas Login | Registro según rol (Doctor/Paciente).
class AuthScreen extends StatefulWidget {
  final bool isDoctor;

  const AuthScreen({super.key, required this.isDoctor});

  @override
  State<AuthScreen> createState() => _AuthScreenState();
}

class _AuthScreenState extends State<AuthScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final roleLabel = widget.isDoctor ? 'Doctor' : 'Paciente';
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: AppColors.black),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: Text(
          roleLabel,
          style: const TextStyle(
            color: AppColors.primary,
            fontWeight: FontWeight.w600,
            fontSize: 18,
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.primary,
          unselectedLabelColor: AppColors.gray,
          indicatorColor: AppColors.primary,
          tabs: const [
            Tab(text: 'Iniciar sesión'),
            Tab(text: 'Registrarse'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _LoginTab(
            emailController: _emailController,
            passwordController: _passwordController,
            obscurePassword: _obscurePassword,
            onTogglePassword: () =>
                setState(() => _obscurePassword = !_obscurePassword),
            onLogin: _login,
          ),
          _RegisterTab(
            nameController: _nameController,
            emailController: _emailController,
            passwordController: _passwordController,
            phoneController: _phoneController,
            obscurePassword: _obscurePassword,
            onTogglePassword: () =>
                setState(() => _obscurePassword = !_obscurePassword),
            onRegister: _register,
          ),
        ],
      ),
    );
  }

  void _login() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text;
    if (email.isEmpty || password.isEmpty) {
      _showSnack('Ingresa email y contraseña');
      return;
    }
    _showSnack('Conectando...', isError: false);
    try {
      await AuthRepository.login(email: email, password: password);
      if (!mounted) return;
      Navigator.of(context).pushNamedAndRemoveUntil('/', (route) => false);
    } on ApiException catch (e) {
      if (mounted) _showSnack(e.message);
    } catch (_) {
      if (mounted) _showSnack('Revisa tu conexión o el servidor.');
    }
  }

  void _register() async {
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text;
    final confirm = password; // API pide password_confirmation; mismo valor si no hay campo repetir
    if (name.isEmpty || email.isEmpty || password.isEmpty) {
      _showSnack('Completa todos los campos');
      return;
    }
    if (password.length < 8) {
      _showSnack('La contraseña debe tener al menos 8 caracteres');
      return;
    }
    if (password != confirm) {
      _showSnack('Las contraseñas no coinciden');
      return;
    }
    final role = widget.isDoctor ? 'doctor' : 'patient';
    _showSnack('Registrando...', isError: false);
    try {
      await AuthRepository.register(
        name: name,
        email: email,
        password: password,
        passwordConfirmation: confirm,
        role: role,
      );
      if (!mounted) return;
      Navigator.of(context).pushNamedAndRemoveUntil('/', (route) => false);
    } on ApiException catch (e) {
      if (mounted) _showSnack(e.message);
    } catch (_) {
      if (mounted) _showSnack('Revisa tu conexión o el servidor.');
    }
  }

  void _showSnack(String message, {bool isError = true}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? AppColors.red : AppColors.primary,
      ),
    );
  }
}

class _LoginTab extends StatelessWidget {
  final TextEditingController emailController;
  final TextEditingController passwordController;
  final bool obscurePassword;
  final VoidCallback onTogglePassword;
  final VoidCallback onLogin;

  const _LoginTab({
    required this.emailController,
    required this.passwordController,
    required this.obscurePassword,
    required this.onTogglePassword,
    required this.onLogin,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text(
            'Bienvenido',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w600,
              color: AppColors.primary,
            ),
          ),
          const Text(
            'Iniciar sesión',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w600,
              color: AppColors.black,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Ingresa con tu correo y contraseña.',
            style: TextStyle(fontSize: 14, color: AppColors.gray),
          ),
          const SizedBox(height: 28),
          _LabeledField(
            label: 'Correo',
            hint: 'Tu correo electrónico',
            controller: emailController,
            keyboardType: TextInputType.emailAddress,
          ),
          const SizedBox(height: 16),
          _LabeledField(
            label: 'Contraseña',
            hint: 'Tu contraseña',
            controller: passwordController,
            obscureText: obscurePassword,
            suffix: IconButton(
              icon: Icon(
                obscurePassword ? Icons.visibility_off : Icons.visibility,
                color: AppColors.gray,
                size: 22,
              ),
              onPressed: onTogglePassword,
            ),
          ),
          const SizedBox(height: 8),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton(
              onPressed: () {},
              child: const Text(
                '¿Olvidaste tu contraseña?',
                style: TextStyle(color: AppColors.black, fontSize: 14),
              ),
            ),
          ),
          const SizedBox(height: 24),
          SizedBox(
            height: 50,
            child: ElevatedButton(
              onPressed: onLogin,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text('Entrar'),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            'O continúa con',
            textAlign: TextAlign.center,
            style: TextStyle(color: AppColors.gray, fontSize: 14),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _SocialButton(icon: Icons.facebook),
              const SizedBox(width: 16),
              _SocialButton(icon: Icons.g_mobiledata),
            ],
          ),
        ],
      ),
    );
  }
}

class _RegisterTab extends StatelessWidget {
  final TextEditingController nameController;
  final TextEditingController emailController;
  final TextEditingController passwordController;
  final TextEditingController phoneController;
  final bool obscurePassword;
  final VoidCallback onTogglePassword;
  final VoidCallback onRegister;

  const _RegisterTab({
    required this.nameController,
    required this.emailController,
    required this.passwordController,
    required this.phoneController,
    required this.obscurePassword,
    required this.onTogglePassword,
    required this.onRegister,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text(
            'Crear cuenta',
            style: TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w600,
              color: AppColors.primary,
            ),
          ),
          const SizedBox(height: 24),
          _LabeledField(
            label: 'Nombre completo',
            hint: 'Tu nombre completo',
            controller: nameController,
          ),
          const SizedBox(height: 16),
          _LabeledField(
            label: 'Correo electrónico',
            hint: 'Tu correo electrónico',
            controller: emailController,
            keyboardType: TextInputType.emailAddress,
          ),
          const SizedBox(height: 16),
          _LabeledField(
            label: 'Contraseña',
            hint: 'Tu contraseña',
            controller: passwordController,
            obscureText: obscurePassword,
            suffix: IconButton(
              icon: Icon(
                obscurePassword ? Icons.visibility_off : Icons.visibility,
                color: AppColors.gray,
                size: 22,
              ),
              onPressed: onTogglePassword,
            ),
          ),
          const SizedBox(height: 16),
          _LabeledField(
            label: 'Teléfono',
            hint: 'Tu número de teléfono',
            controller: phoneController,
            keyboardType: TextInputType.phone,
          ),
          const SizedBox(height: 28),
          SizedBox(
            height: 50,
            child: ElevatedButton(
              onPressed: onRegister,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: AppColors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              child: const Text('Registrarse'),
            ),
          ),
          const SizedBox(height: 24),
          Text(
            'O continúa con',
            textAlign: TextAlign.center,
            style: TextStyle(color: AppColors.gray, fontSize: 14),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _SocialButton(icon: Icons.facebook),
              const SizedBox(width: 16),
              _SocialButton(icon: Icons.g_mobiledata),
            ],
          ),
        ],
      ),
    );
  }
}

class _LabeledField extends StatelessWidget {
  final String label;
  final String hint;
  final TextEditingController controller;
  final bool obscureText;
  final TextInputType? keyboardType;
  final Widget? suffix;

  const _LabeledField({
    required this.label,
    required this.hint,
    required this.controller,
    this.obscureText = false,
    this.keyboardType,
    this.suffix,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w500,
            color: AppColors.black,
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          obscureText: obscureText,
          keyboardType: keyboardType,
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: TextStyle(color: AppColors.gray, fontSize: 14),
            filled: true,
            fillColor: AppColors.inputFill,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: AppColors.inputBorder),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: AppColors.inputBorder),
            ),
            suffixIcon: suffix,
          ),
        ),
      ],
    );
  }
}

class _SocialButton extends StatelessWidget {
  final IconData icon;

  const _SocialButton({required this.icon});

  @override
  Widget build(BuildContext context) {
    return OutlinedButton(
      onPressed: () {},
      style: OutlinedButton.styleFrom(
        padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 14),
        side: const BorderSide(color: AppColors.inputBorder),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
        ),
      ),
      child: Icon(icon, size: 28, color: AppColors.gray),
    );
  }
}
